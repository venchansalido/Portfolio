<?php
session_start();
include '../includes/config.php';
include '../includes/restriction_admin.php';
include '../includes/session_utils.php';

// Strict session check for admin pages
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || !checkSessionExpiration()) {
    session_unset();
    session_destroy();
    header('Location: ../index.php');
    exit;
}

// Function to handle file uploads
function uploadFile($file, $uploadDir, $prefix, $projectId = null) {
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = $prefix . '_' . time() . '_' . ($projectId ?? '') . '_' . uniqid() . '.' . $fileExtension;
    $filePath = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return $filePath;
    }
    return false;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_project']) || isset($_POST['update_project'])) {
        $isUpdate = isset($_POST['update_project']);
        $title = $_POST['title'];
        $description = $_POST['description'];
        $url = $_POST['url'] ?? null;
        $id = $isUpdate ? $_POST['id'] : null;
        
        // Validate inputs
        if (empty($title) || empty($description)) {
            $_SESSION['error'] = "Title and description are required!";
            header("Location: projects.php");
            exit();
        }
        
        // Handle thumbnail upload
        $thumbnailPath = null;
        if ($isUpdate) {
            // Get current thumbnail path for updates
            $stmt = $conn->prepare("SELECT thumbnail_path FROM projects WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $thumbnailPath = $result->num_rows > 0 ? $result->fetch_assoc()['thumbnail_path'] : null;
        }
        
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            // Delete old thumbnail if exists (for updates)
            if ($isUpdate && $thumbnailPath && file_exists($thumbnailPath)) {
                unlink($thumbnailPath);
            }
            
            // Upload new thumbnail
            $newThumbnail = uploadFile(
                $_FILES['thumbnail'],
                '../assets/images/projects/',
                'thumb',
                $id
            );
            
            if ($newThumbnail === false) {
                $_SESSION['error'] = "Failed to upload thumbnail!";
                header("Location: projects.php");
                exit();
            }
            $thumbnailPath = $newThumbnail;
        }
        
        // Handle project create/update
        if ($isUpdate) {
            $stmt = $conn->prepare("UPDATE projects SET title = ?, description = ?, thumbnail_path = ?, url = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $title, $description, $thumbnailPath, $url, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO projects (title, description, thumbnail_path, url) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $title, $description, $thumbnailPath, $url);
        }
        
        if ($stmt->execute()) {
            $projectId = $isUpdate ? $id : $conn->insert_id;
            
            // Handle gallery images upload
            if (!empty($_FILES['gallery_images']['name'][0])) {
                $uploadDir = '../assets/images/projects/gallery/';
                
                foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmpName) {
                    if ($_FILES['gallery_images']['error'][$key] === UPLOAD_ERR_OK) {
                        $imagePath = uploadFile(
                            [
                                'name' => $_FILES['gallery_images']['name'][$key],
                                'tmp_name' => $tmpName,
                                'error' => $_FILES['gallery_images']['error'][$key]
                            ],
                            $uploadDir,
                            'gallery',
                            $projectId
                        );
                        
                        if ($imagePath) {
                            $stmt = $conn->prepare("INSERT INTO project_gallery (project_id, image_path) VALUES (?, ?)");
                            $stmt->bind_param("is", $projectId, $imagePath);
                            if (!$stmt->execute()) {
                                $_SESSION['error'] = "Error adding gallery image: " . $conn->error;
                            }
                        }
                    }
                }
            }
            
            $_SESSION['success'] = $isUpdate ? "Project updated successfully!" : "Project added successfully!";
        } else {
            $_SESSION['error'] = $isUpdate ? "Error updating project: " . $conn->error : "Error adding project: " . $conn->error;
        }
        
        header("Location: projects.php");
        exit();
    }
}

// Handle delete action
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Validate ID
    if (!is_numeric($id)) {
        $_SESSION['error'] = "Invalid project ID!";
        header("Location: projects.php");
        exit();
    }
    
    // Get thumbnail path before deletion
    $stmt = $conn->prepare("SELECT thumbnail_path FROM projects WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $thumbnailPath = $result->fetch_assoc()['thumbnail_path'];
    
    // Get gallery images before deletion
    $galleryImages = [];
    $stmt = $conn->prepare("SELECT image_path FROM project_gallery WHERE project_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $galleryImages[] = $row['image_path'];
    }
    
    // Delete the project (cascade will delete gallery images)
    $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Delete associated thumbnail if exists
        if ($thumbnailPath && file_exists($thumbnailPath)) {
            unlink($thumbnailPath);
        }
        
        // Delete gallery images
        foreach ($galleryImages as $imagePath) {
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        $_SESSION['success'] = "Project deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting project: " . $conn->error;
    }
    
    header("Location: projects.php");
    exit();
}

// Handle gallery image deletion
if (isset($_GET['delete_image'])) {
    $imageId = $_GET['delete_image'];
    $projectId = $_GET['project_id'];
    
    // Get image path before deletion
    $stmt = $conn->prepare("SELECT image_path FROM project_gallery WHERE id = ?");
    $stmt->bind_param("i", $imageId);
    $stmt->execute();
    $result = $stmt->get_result();
    $imagePath = $result->fetch_assoc()['image_path'];
    
    // Delete the image
    $stmt = $conn->prepare("DELETE FROM project_gallery WHERE id = ?");
    $stmt->bind_param("i", $imageId);
    
    if ($stmt->execute()) {
        // Delete image file
        if ($imagePath && file_exists($imagePath)) {
            unlink($imagePath);
        }
        $_SESSION['success'] = "Image deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting image: " . $conn->error;
    }
    
    header("Location: projects.php?edit=" . $projectId);
    exit();
}

// Fetch all projects
$projects = [];
$query = "SELECT * FROM projects ORDER BY created_at DESC";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
}

// Get project for editing if edit_id is set
$editProject = null;
$galleryImages = [];
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    
    // Validate ID
    if (!is_numeric($id)) {
        $_SESSION['error'] = "Invalid project ID!";
        header("Location: projects.php");
        exit();
    }
    
    $stmt = $conn->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Project not found!";
        header("Location: projects.php");
        exit();
    }
    
    $editProject = $result->fetch_assoc();
    
    // Get gallery images for this project
    $stmt = $conn->prepare("SELECT * FROM project_gallery WHERE project_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $galleryImages[] = $row;
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --dark-bg: #121212;
            --darker-bg: #1e1e1e;
            --dark-text: #e0e0e0;
            --accent-color: #00ff99;
            --accent-hover: #66ccff;
            --border-color: #333;
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--dark-text);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

            h2 {
        font-size: 30px;
        color: #00ff99;
        margin-bottom: 20px;
        font-weight: 500;
    }
        
        .card {
            background-color: var(--darker-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: #2a2a2a;
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
            color: var(--accent-color);
        }
        
        .form-control, .form-select, .form-control:disabled {
            background-color: #2a2a2a;
            border: 1px solid #444;
            color: var(--dark-text);
        }
        
        .form-control:focus, .form-select:focus {
            background-color: #333;
            color: var(--dark-text);
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.25rem rgba(0, 255, 153, 0.25);
        }
        
        .btn-primary {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            color: #121212;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background-color: var(--accent-hover);
            border-color: var(--accent-hover);
        }
        
        .btn-outline-primary {
            color: var(--accent-color);
            border-color: var(--accent-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            color: #121212;
        }
        
        .btn-danger {
            background-color: #ff4d4d;
            border-color: #ff4d4d;
        }
        
        .btn-danger:hover {
            background-color: #ff6666;
            border-color: #ff6666;
        }
        
        .table {
            color: var(--dark-text);
            border-color: var(--border-color);
        }
        
        .table th {
            background-color: #2a2a2a;
            border-color: var(--border-color);
        }
        
        .table td {
            border-color: var(--border-color);
            vertical-align: middle;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.4);
        }
        
        .alert {
            border: 1px solid transparent;
        }
        
        .alert-success {
            background-color: rgba(0, 255, 153, 0.1);
            border-color: rgba(0, 255, 153, 0.2);
            color: var(--accent-color);
        }
        
        .alert-danger {
            background-color: rgba(255, 77, 77, 0.1);
            border-color: rgba(255, 77, 77, 0.2);
            color: #ff4d4d;
        }
        
        .alert-info {
            background-color: rgba(66, 165, 245, 0.1);
            border-color: rgba(66, 165, 245, 0.2);
            color: #42a5f5;
        }
        
        .project-card {
            background-color: #2a2a2a;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid var(--accent-color);
        }
        
        .project-image-preview {
            max-width: 100%;
            max-height: 150px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        
        .action-btns .btn {
            margin-right: 5px;
        }
        
        .text-accent {
            color: var(--accent-color);
        }
        
        .toggle-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
            background-color: var(--accent-color);
            color: #121212;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .main-content {
            padding: 20px;
            margin-left: 250px;
            transition: margin-left 0.3s;
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
        }
        
        /* Gallery styles */
        .gallery-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }
        
        .gallery-item {
            position: relative;
            width: 100px;
            height: 100px;
        }
        
        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .gallery-item .delete-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: #ff4d4d;
            color: white;
            border: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            cursor: pointer;
        }
        
        /* Modal styles */
        .modal-content {
            background-color: var(--darker-bg);
            border: 1px solid var(--border-color);
        }
        
        .modal-header, .modal-footer {
            border-color: var(--border-color);
        }
        
        .carousel-control-prev-icon, 
        .carousel-control-next-icon {
            background-color: var(--accent-color);
            border-radius: 50%;
            padding: 10px;
        }
    </style>
</head>
<body>
    <button class="toggle-btn" id="menuToggle"><i class="fas fa-bars"></i></button>

    <div class="wrapper">
        <?php include '../includes/sidebar.php'; ?>

        <div class="main-content">
            <h2 class="mb-4"><i class="fas fa-laptop-code"></i> Projects Management</h2>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <!-- Add/Edit Form Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-plus-circle me-2"></i><?= $editProject ? 'Edit' : 'Add New' ?> Project
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <?php if ($editProject): ?>
                            <input type="hidden" name="id" value="<?= $editProject['id'] ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label">Project Title</label>
                            <input type="text" name="title" class="form-control" value="<?= $editProject ? htmlspecialchars($editProject['title']) : '' ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4" required><?= $editProject ? htmlspecialchars($editProject['description']) : '' ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Project URL (optional)</label>
                            <input type="url" name="url" class="form-control" value="<?= $editProject ? htmlspecialchars($editProject['url']) : '' ?>" placeholder="https://example.com">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Thumbnail Image</label>
                            <input type="file" name="thumbnail" class="form-control" accept="image/*">
                            <?php if ($editProject && $editProject['thumbnail_path']): ?>
                                <div class="mt-2">
                                    <img src="<?= htmlspecialchars($editProject['thumbnail_path']) ?>" class="project-image-preview" alt="Current Thumbnail">
                                    <small class="text-muted d-block">Current thumbnail</small>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Gallery Images (optional)</label>
                            <input type="file" name="gallery_images[]" class="form-control" accept="image/*" multiple>
                            
                            <?php if (!empty($galleryImages)): ?>
                                <div class="gallery-container">
                                    <?php foreach ($galleryImages as $image): ?>
                                        <div class="gallery-item">
                                            <img src="<?= htmlspecialchars($image['image_path']) ?>" alt="Gallery Image">
                                            <a href="projects.php?delete_image=<?= $image['id'] ?>&project_id=<?= $editProject['id'] ?>" 
                                               class="delete-btn" 
                                               title="Delete Image"
                                               onclick="return confirm('Are you sure you want to delete this image?')">
                                                ×
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <?php if ($editProject): ?>
                                <a href="projects.php" class="btn btn-outline-secondary me-2">Cancel</a>
                            <?php endif; ?>
                            <button type="submit" name="<?= $editProject ? 'update_project' : 'add_project' ?>" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i><?= $editProject ? 'Update' : 'Add' ?> Project
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Projects List -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list me-2"></i>Current Projects
                </div>
                <div class="card-body">
                    <?php if (empty($projects)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>No projects found. Add some using the form above.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Thumbnail</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Gallery Images</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($projects as $project): ?>
                                        <?php 
                                        // Get gallery images count for this project
                                        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM project_gallery WHERE project_id = ?");
                                        $stmt->bind_param("i", $project['id']);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        $galleryCount = $result->fetch_assoc()['count'];
                                        ?>
                                        <tr>
                                            <td>
                                                <?php if ($project['thumbnail_path']): ?>
                                                    <img src="<?= htmlspecialchars($project['thumbnail_path']) ?>" class="project-image-preview" alt="Thumbnail">
                                                <?php else: ?>
                                                    <i class="fas fa-image text-muted"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($project['title']) ?></td>
                                            <td><?= htmlspecialchars(substr($project['description'], 0, 50)) ?>...</td>
                                            <td><?= $galleryCount ?> images</td>
                                            <td class="action-btns">
                                                <a href="projects.php?edit=<?= $project['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="projects.php?delete=<?= $project['id'] ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this project?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');

        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    </script>
</body>
</html>