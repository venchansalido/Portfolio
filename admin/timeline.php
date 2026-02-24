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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_timeline'])) {
        // Add new timeline item
        $position = $_POST['position'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $status = $_POST['status'];
        
        $stmt = $conn->prepare("INSERT INTO timeline_items (position, title, description, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $position, $title, $description, $status);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Timeline item added successfully!";
        } else {
            $_SESSION['error'] = "Error adding timeline item: " . $conn->error;
        }
    } elseif (isset($_POST['update_timeline'])) {
        // Update existing timeline item
        $id = $_POST['id'];
        $position = $_POST['position'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $status = $_POST['status'];
        
        $stmt = $conn->prepare("UPDATE timeline_items SET position = ?, title = ?, description = ?, status = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $position, $title, $description, $status, $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Timeline item updated successfully!";
        } else {
            $_SESSION['error'] = "Error updating timeline item: " . $conn->error;
        }
    }
}

// Handle delete action
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    $stmt = $conn->prepare("DELETE FROM timeline_items WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Timeline item deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting timeline item: " . $conn->error;
    }
    
    header("Location: timeline.php");
    exit();
}

// Fetch all timeline items
$timelineItems = [];
$query = "SELECT * FROM timeline_items ORDER BY created_at DESC";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $timelineItems[] = $row;
    }
}

// Get item for editing if edit_id is set
$editItem = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM timeline_items WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editItem = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timeline Management</title>
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
            background-color: rgba(255, 255, 255, 0.05);
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
        
        .timeline-item-card {
            background-color: #2a2a2a;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid var(--accent-color);
        }
        
        .timeline-item-card.right {
            border-left-color: var(--accent-hover);
        }
        
        .timeline-item-title {
            color: var(--accent-color);
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .timeline-item-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            background-color: rgba(0, 255, 153, 0.1);
            color: var(--accent-color);
            font-size: 0.8rem;
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
    </style>
</head>
<body>
    <button class="toggle-btn" id="menuToggle"><i class="fas fa-bars"></i></button>

    <div class="wrapper">
        <?php include '../includes/sidebar.php'; ?>

        <div class="main-content">
            <h2 class="mb-4"><i class="fas fa-briefcase"></i> Timeline Management</h2>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <!-- Add/Edit Form Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-plus-circle me-2"></i><?= $editItem ? 'Edit' : 'Add New' ?> Timeline Item
                </div>
                <div class="card-body">
                    <form method="POST">
                        <?php if ($editItem): ?>
                            <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
                        <?php endif; ?>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Position</label>
                                <select name="position" class="form-select" required>
                                    <option value="left" <?= ($editItem && $editItem['position'] == 'left') ? 'selected' : '' ?>>Left</option>
                                    <option value="right" <?= ($editItem && $editItem['position'] == 'right') ? 'selected' : '' ?>>Right</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <input type="text" name="status" class="form-control" value="<?= $editItem ? htmlspecialchars($editItem['status']) : 'on progress' ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" value="<?= $editItem ? htmlspecialchars($editItem['title']) : '' ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4" required><?= $editItem ? htmlspecialchars($editItem['description']) : '' ?></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <?php if ($editItem): ?>
                                <a href="timeline.php" class="btn btn-outline-secondary me-2">Cancel</a>
                            <?php endif; ?>
                            <button type="submit" name="<?= $editItem ? 'update_timeline' : 'add_timeline' ?>" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i><?= $editItem ? 'Update' : 'Add' ?> Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Timeline Items List -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list me-2"></i>Current Timeline Items
                </div>
                <div class="card-body">
                    <?php if (empty($timelineItems)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>No timeline items found. Add some using the form above.
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($timelineItems as $item): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="timeline-item-card <?= $item['position'] ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h5 class="timeline-item-title"><?= htmlspecialchars($item['title']) ?></h5>
                                                <span class="timeline-item-status"><?= htmlspecialchars($item['status']) ?></span>
                                                <p class="mb-0"><?= htmlspecialchars($item['description']) ?></p>
                                            </div>
                                            <div class="action-btns">
                                                <a href="timeline.php?edit=<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="timeline.php?delete=<?= $item['id'] ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this item?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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