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

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: ../user/login.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Handle Skills CRUD
if (isset($_POST['add_skill'])) {
    $skillName = $_POST['skill_name'];
    $iconPath = '';
    
    // Handle icon upload
    if (isset($_FILES['skill_icon']) && $_FILES['skill_icon']['error'] == 0) {
        $iconName = basename($_FILES['skill_icon']['name']);
        $iconPath = "../assets/images/skill_cms/" . $iconName;
        move_uploaded_file($_FILES['skill_icon']['tmp_name'], $iconPath);
    }
    
    $stmt = $conn->prepare("INSERT INTO user_skills (user_id, skill_name, icon_path) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $userId, $skillName, $iconPath);
    $stmt->execute();
}

if (isset($_POST['update_skill'])) {
    $skillId = $_POST['skill_id'];
    $skillName = $_POST['skill_name'];
    
    // Handle icon update
    if (isset($_FILES['skill_icon']) && $_FILES['skill_icon']['error'] == 0) {
        $iconName = basename($_FILES['skill_icon']['name']);
        $iconPath = "../assets/images/skill_cms/" . $iconName;
        move_uploaded_file($_FILES['skill_icon']['tmp_name'], $iconPath);
        
        $stmt = $conn->prepare("UPDATE user_skills SET skill_name = ?, icon_path = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssii", $skillName, $iconPath, $skillId, $userId);
    } else {
        $stmt = $conn->prepare("UPDATE user_skills SET skill_name = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $skillName, $skillId, $userId);
    }
    $stmt->execute();
}

if (isset($_GET['delete_skill'])) {
    $skillId = $_GET['delete_skill'];
    $stmt = $conn->prepare("DELETE FROM user_skills WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $skillId, $userId);
    $stmt->execute();
}

// Handle Education CRUD
if (isset($_POST['add_education'])) {
    $level = $_POST['level'];
    $schoolName = $_POST['school_name'];
    $course = $_POST['course'] ?? null;
    $address = $_POST['address'] ?? null;
    $startYear = $_POST['start_year'];
    $endYear = $_POST['end_year'];
    $imagePath = '';
    
    // Handle image upload
    if (isset($_FILES['education_image']) && $_FILES['education_image']['error'] == 0) {
        $imageName = basename($_FILES['education_image']['name']);
        $imagePath = "../assets/images/edu_cms/" . $imageName;
        move_uploaded_file($_FILES['education_image']['tmp_name'], $imagePath);
    }
    
    $stmt = $conn->prepare("INSERT INTO user_education (user_id, level, school_name, course, address, start_year, end_year, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssiis", $userId, $level, $schoolName, $course, $address, $startYear, $endYear, $imagePath);
    $stmt->execute();
}

if (isset($_POST['update_education'])) {
    $eduId = $_POST['edu_id'];
    $level = $_POST['level'];
    $schoolName = $_POST['school_name'];
    $course = $_POST['course'] ?? null;
    $address = $_POST['address'] ?? null;
    $startYear = $_POST['start_year'];
    $endYear = $_POST['end_year'];
    
    // Handle image update
    if (isset($_FILES['education_image']) && $_FILES['education_image']['error'] == 0) {
        $imageName = basename($_FILES['education_image']['name']);
        $imagePath = "../assets/images/edu_cms/" . $imageName;
        move_uploaded_file($_FILES['education_image']['tmp_name'], $imagePath);
        
        $stmt = $conn->prepare("UPDATE user_education SET level = ?, school_name = ?, course = ?, address = ?, start_year = ?, end_year = ?, image_path = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssssiisii", $level, $schoolName, $course, $address, $startYear, $endYear, $imagePath, $eduId, $userId);
    } else {
        $stmt = $conn->prepare("UPDATE user_education SET level = ?, school_name = ?, course = ?, address = ?, start_year = ?, end_year = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sssssiii", $level, $schoolName, $course, $address, $startYear, $endYear, $eduId, $userId);
    }
    $stmt->execute();
}

if (isset($_GET['delete_education'])) {
    $eduId = $_GET['delete_education'];
    $stmt = $conn->prepare("DELETE FROM user_education WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $eduId, $userId);
    $stmt->execute();
}

// Fetch existing data
$skills = $conn->query("SELECT * FROM user_skills WHERE user_id = $userId")->fetch_all(MYSQLI_ASSOC);
$education = $conn->query("SELECT * FROM user_education WHERE user_id = $userId ORDER BY FIELD(level, 'Primary', 'Secondary', 'Tertiary')")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Skills & Education</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --dark-bg: #121212;
            --darker-bg: #1e1e1e;
            --dark-text: #e0e0e0;
            --accent-color: #00ff99;
            --accent-hover: #66ccff;
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
            border: 1px solid #333;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: #2a2a2a;
            border-bottom: 1px solid #333;
            font-weight: 600;
        }
        
        .form-control, .form-select {
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
        
        .btn-danger {
            background-color: #ff4d4d;
            border-color: #ff4d4d;
        }
        
        .table {
            color: var(--dark-text);
        }


        
        
        .table th {
            background-color: #2a2a2a;
            border-color: #444;
        }
        
        .table td {
            border-color: #444;
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.4);
        }
        
        .skill-icon {
            width: 40px;
            height: 40px;
            object-fit: contain;
            margin-right: 10px;
        }
        
        .edu-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .action-btns .btn {
            margin-right: 5px;
        }
        
        .modal-content {
            background-color: var(--darker-bg);
            border: 1px solid #444;
        }
        
        .modal-header {
            border-bottom: 1px solid #444;
        }
        
        .modal-footer {
            border-top: 1px solid #444;
        }
        
        .nav-tabs .nav-link {
            color: var(--dark-text);
        }
        
        .nav-tabs .nav-link.active {
            background-color: var(--darker-bg);
            border-color: #444 #444 var(--darker-bg);
            color: var(--accent-color);
            font-weight: 600;
        }
        
        .nav-tabs {
            border-bottom: 1px solid #444;
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
    </style>
</head>
<body>
    <button class="toggle-btn" id="menuToggle"><i class="fas fa-bars"></i></button>

    <div class="wrapper">
        <?php include '../includes/sidebar.php'; ?>

        <div class="main-content">
            <h2 class="mb-4"><i class="fas fa-laptop-code"></i> Manage Skills & Education</h2>
            
            <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="skills-tab" data-bs-toggle="tab" data-bs-target="#skills" type="button" role="tab">Skills</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="education-tab" data-bs-toggle="tab" data-bs-target="#education" type="button" role="tab">Education</button>
                </li>
            </ul>
            
            <div class="tab-content" id="myTabContent">
                <!-- Skills Tab -->
                <div class="tab-pane fade show active" id="skills" role="tabpanel">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-laptop-code me-2"></i>Add New Skill</span>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Skill Name</label>
                                            <input type="text" name="skill_name" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Skill Icon</label>
                                            <input type="file" name="skill_icon" class="form-control" accept="image/*">
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" name="add_skill" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Add Skill
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <span><i class="fas fa-list me-2"></i>Your Skills</span>
                        </div>
                        <div class="card-body">
                            <?php if (empty($skills)): ?>
                                <div class="alert alert-info">No skills added yet.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Icon</th>
                                                <th>Skill Name</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($skills as $skill): ?>
                                                <tr>
                                                    <td>
                                                        <?php if (!empty($skill['icon_path'])): ?>
                                                            <img src="<?= htmlspecialchars($skill['icon_path']) ?>" class="skill-icon" alt="<?= htmlspecialchars($skill['skill_name']) ?>">
                                                        <?php else: ?>
                                                            <i class="fas fa-code skill-icon"></i>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($skill['skill_name']) ?></td>
                                                    <td class="action-btns">
                                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editSkillModal<?= $skill['id'] ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <a href="?delete_skill=<?= $skill['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this skill?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                
                                                <!-- Edit Skill Modal -->
                                                <div class="modal fade" id="editSkillModal<?= $skill['id'] ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Edit Skill</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form method="POST" enctype="multipart/form-data">
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="skill_id" value="<?= $skill['id'] ?>">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Skill Name</label>
                                                                        <input type="text" name="skill_name" class="form-control" value="<?= htmlspecialchars($skill['skill_name']) ?>" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Skill Icon</label>
                                                                        <?php if (!empty($skill['icon_path'])): ?>
                                                                            <div class="mb-2">
                                                                                <img src="<?= htmlspecialchars($skill['icon_path']) ?>" class="skill-icon" alt="Current Icon">
                                                                            </div>
                                                                        <?php endif; ?>
                                                                        <input type="file" name="skill_icon" class="form-control" accept="image/*">
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                    <button type="submit" name="update_skill" class="btn btn-primary">Save Changes</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Education Tab -->
                <div class="tab-pane fade" id="education" role="tabpanel">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-graduation-cap me-2"></i>Add New Education</span>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Education Level</label>
                                            <select name="level" class="form-select" required>
                                                <option value="Primary">Primary</option>
                                                <option value="Secondary">Secondary</option>
                                                <option value="Tertiary">Tertiary</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">School Name</label>
                                            <input type="text" name="school_name" class="form-control" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Course (if applicable)</label>
                                            <input type="text" name="course" class="form-control">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Address</label>
                                            <input type="text" name="address" class="form-control">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Start Year</label>
                                            <input type="number" name="start_year" min="1900" max="2099" class="form-control" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">End Year</label>
                                            <input type="number" name="end_year" min="1900" max="2099" class="form-control" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Image</label>
                                            <input type="file" name="education_image" class="form-control" accept="image/*">
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" name="add_education" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Add Education
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <span><i class="fas fa-list me-2"></i>Your Education</span>
                        </div>
                        <div class="card-body">
                            <?php if (empty($education)): ?>
                                <div class="alert alert-info">No education entries added yet.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Image</th>
                                                <th>Level</th>
                                                <th>School</th>
                                                <th>Years</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($education as $edu): ?>
                                                <tr>
                                                    <td>
                                                        <?php if (!empty($edu['image_path'])): ?>
                                                            <img src="<?= htmlspecialchars($edu['image_path']) ?>" class="edu-image" alt="School Image">
                                                        <?php else: ?>
                                                            <i class="fas fa-school edu-image"></i>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($edu['level']) ?></td>
                                                    <td>
                                                        <?= htmlspecialchars($edu['school_name']) ?>
                                                        <?php if (!empty($edu['course'])): ?>
                                                            <br><small><?= htmlspecialchars($edu['course']) ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($edu['start_year']) ?> - <?= htmlspecialchars($edu['end_year']) ?></td>
                                                    <td class="action-btns">
                                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editEduModal<?= $edu['id'] ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <a href="?delete_education=<?= $edu['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this education entry?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                
                                                <!-- Edit Education Modal -->
                                                <div class="modal fade" id="editEduModal<?= $edu['id'] ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Edit Education</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form method="POST" enctype="multipart/form-data">
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="edu_id" value="<?= $edu['id'] ?>">
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <div class="mb-3">
                                                                                <label class="form-label">Education Level</label>
                                                                                <select name="level" class="form-select" required>
                                                                                    <option value="Primary" <?= $edu['level'] == 'Primary' ? 'selected' : '' ?>>Primary</option>
                                                                                    <option value="Secondary" <?= $edu['level'] == 'Secondary' ? 'selected' : '' ?>>Secondary</option>
                                                                                    <option value="Tertiary" <?= $edu['level'] == 'Tertiary' ? 'selected' : '' ?>>Tertiary</option>
                                                                                </select>
                                                                            </div>
                                                                            
                                                                            <div class="mb-3">
                                                                                <label class="form-label">School Name</label>
                                                                                <input type="text" name="school_name" class="form-control" value="<?= htmlspecialchars($edu['school_name']) ?>" required>
                                                                            </div>
                                                                            
                                                                            <div class="mb-3">
                                                                                <label class="form-label">Course (if applicable)</label>
                                                                                <input type="text" name="course" class="form-control" value="<?= htmlspecialchars($edu['course'] ?? '') ?>">
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <div class="col-md-6">
                                                                            <div class="mb-3">
                                                                                <label class="form-label">Address</label>
                                                                                <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($edu['address'] ?? '') ?>">
                                                                            </div>
                                                                            
                                                                            <div class="mb-3">
                                                                                <label class="form-label">Start Year</label>
                                                                                <input type="number" name="start_year" min="1900" max="2099" class="form-control" value="<?= htmlspecialchars($edu['start_year']) ?>" required>
                                                                            </div>
                                                                            
                                                                            <div class="mb-3">
                                                                                <label class="form-label">End Year</label>
                                                                                <input type="number" name="end_year" min="1900" max="2099" class="form-control" value="<?= htmlspecialchars($edu['end_year']) ?>" required>
                                                                            </div>
                                                                            
                                                                            <div class="mb-3">
                                                                                <label class="form-label">Image</label>
                                                                                <?php if (!empty($edu['image_path'])): ?>
                                                                                    <div class="mb-2">
                                                                                        <img src="<?= htmlspecialchars($edu['image_path']) ?>" class="edu-image" alt="Current Image">
                                                                                    </div>
                                                                                <?php endif; ?>
                                                                                <input type="file" name="education_image" class="form-control" accept="image/*">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                    <button type="submit" name="update_education" class="btn btn-primary">Save Changes</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
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
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    </script>
</body>
</html>