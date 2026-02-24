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

// Fetch user data
$userId = $_SESSION['user_id'] ?? 1; // Assuming user ID 1 is the profile to edit
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic info
    $firstName = $_POST['first_name'];
    $middleName = $_POST['middle_name'];
    $lastName = $_POST['last_name'];
    $caption = $_POST['caption'];
    $bio = $_POST['bio'];
    
    // Contact info
    $email = $_POST['email'];
    $phone = $_POST['phone_number'];
    $address = $_POST['street_address'];
    $city = $_POST['city'];
    
    // Personal info
    $birthDate = $_POST['birth_date'];
    $age = $_POST['age'];
    $birthPlace = $_POST['birth_place'];
    $nationality = $_POST['nationality'];
    $religion = $_POST['religion'];

    // In the form submission handling section, update the query to:
    $updateQuery = "UPDATE users SET 
    first_name = ?, 
    middle_name = ?, 
    last_name = ?, 
    caption = ?, 
    bio = ?, 
    email = ?, 
    phone_number = ?, 
    street_address = ?, 
    city = ?, 
    birth_date = ?, 
    age = ?, 
    birth_place = ?, 
    nationality = ?, 
    religion = ? 
    WHERE id = ?";

    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ssssssssssssssi", 
    $firstName, $middleName, $lastName, 
    $caption, $bio, $email, $phone, $address, $city, 
    $birthDate, $age, $birthPlace, $nationality, $religion, 
    $userId
);

    
    if ($stmt->execute()) {
        $success = "Profile updated successfully!";
        
        // Handle profile photo upload
        if (isset($_FILES['profile_photo'])) {
            $imageTmp = $_FILES['profile_photo']['tmp_name'];
            $imageName = basename($_FILES['profile_photo']['name']);
            $imagePath = "../assets/images/profiles/" . $imageName;
            
            if (move_uploaded_file($imageTmp, $imagePath)) {
                // Update profile photo path in database
                $updatePhoto = $conn->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
                $updatePhoto->bind_param("si", $imagePath, $userId);
                $updatePhoto->execute();
                $success .= " Profile photo updated!";
            }
        }
        
        // Handle multiple image uploads for gallery
        if (isset($_FILES['gallery_images'])) {
            foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['gallery_images']['error'][$key] == 0) {
                    $imageName = basename($_FILES['gallery_images']['name'][$key]);
                    $imagePath = "../assets/images/gallery/" . $imageName;
                    
                    if (move_uploaded_file($tmp_name, $imagePath)) {
                        // Store in database
                        $insert = $conn->prepare("INSERT INTO user_gallery (user_id, image_path) VALUES (?, ?)");
                        $insert->bind_param("is", $userId, $imagePath);
                        $insert->execute();
                    }
                }
            }
            $success .= " Gallery images uploaded!";
        }
    } else {
        $error = "Error updating profile: " . $conn->error;
    }
    
    // Refresh user data
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}

// Handle gallery image deletion
if (isset($_GET['delete_image_id'])) {
    $imageId = $_GET['delete_image_id'];
    
    // Get image path
    $query = "SELECT image_path FROM user_gallery WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $imageId);
    $stmt->execute();
    $result = $stmt->get_result();
    $image = $result->fetch_assoc();
    
    // Delete file
    if (file_exists($image['image_path'])) {
        unlink($image['image_path']);
    }
    
    // Delete from database
    $delete = $conn->prepare("DELETE FROM user_gallery WHERE id = ?");
    $delete->bind_param("i", $imageId);
    $delete->execute();
    
    $success = "Image deleted successfully!";
}

// Fetch gallery images
$galleryQuery = "SELECT * FROM user_gallery WHERE user_id = ?";
$galleryStmt = $conn->prepare($galleryQuery);
$galleryStmt->bind_param("i", $userId);
$galleryStmt->execute();
$galleryResult = $galleryStmt->get_result();
$galleryImages = [];
while ($row = $galleryResult->fetch_assoc()) {
    $galleryImages[] = $row;
}


// Handle cover photo upload
if (isset($_FILES['cover_photo']) && $_FILES['cover_photo']['error'] == 0) {
    $coverTmp = $_FILES['cover_photo']['tmp_name'];
    $coverName = basename($_FILES['cover_photo']['name']);
    $coverPath = "../assets/images/covers/" . $coverName;

    if (move_uploaded_file($coverTmp, $coverPath)) {
        // Update cover photo path in database
        $updateCover = $conn->prepare("UPDATE users SET cover_photo = ? WHERE id = ?");
        $updateCover->bind_param("si", $coverPath, $userId);
        $updateCover->execute();
        $success .= " Cover photo updated!";
    } else {
        $error = "Failed to upload cover photo.";
    }
}


// Handle resume upload
if (isset($_FILES['resume']) && $_FILES['resume']['error'] == 0) {
    $resumeTmp = $_FILES['resume']['tmp_name'];
    $resumeName = basename($_FILES['resume']['name']);
    $resumePath = "../assets/resume/" . $resumeName;

    if (move_uploaded_file($resumeTmp, $resumePath)) {
        // Update resume path in database
        $updateResume = $conn->prepare("UPDATE users SET resume_path = ? WHERE id = ?");
        $updateResume->bind_param("si", $resumePath, $userId);
        $updateResume->execute();
        $success .= " Resume updated!";
    } else {
        $error = "Failed to upload resume.";
    }
}

// Handle resume deletion
if (isset($_GET['delete_resume'])) {
    if (!empty($user['resume_path'])) {
        // Delete file
        if (file_exists($user['resume_path'])) {
            unlink($user['resume_path']);
        }
        
        // Update database
        $deleteResume = $conn->prepare("UPDATE users SET resume_path = NULL WHERE id = ?");
        $deleteResume->bind_param("i", $userId);
        $deleteResume->execute();
        
        $success = "Resume deleted successfully!";
        
        // Refresh user data
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        // Redirect to avoid resubmission
        header("Location: profile.php");
        exit();
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
    body {
        background: linear-gradient(135deg, #121212, #1e1e1e); /* Subtle gradient for depth */
        font-family: 'Roboto', sans-serif;
        color: white;
        height: 100vh;
        margin: 0;
        display: flex;
        flex-direction: column;
    }

    .wrapper {
        display: flex;
        flex: 1;
    }

    .main-content {
        flex: 1;
        padding: 40px;
        overflow-y: auto;
        background-color: #181818; /* Slightly lighter background for content area */
        border-radius: 10px;
    }

    h2 {
        font-size: 30px;
        color: #00ff99;
        margin-bottom: 20px;
        font-weight: 500;
    }

    .form-label {
        color: #e0e0e0; /* Light gray for labels */
        font-weight: 500;
    }

    .form-control {
        background-color: #333;
        color: #fff;
        border: 1px solid #444;
        border-radius: 8px;
        padding: 10px;
        font-size: 14px;
        width: 100%;
        box-sizing: border-box;
        transition: background-color 0.3s, border-color 0.3s;
    }

    .form-control:focus {
        background-color: #444;
        border-color: #00bcd4;
        outline: none;
        color:rgb(218, 210, 210);
    }

    .image-preview {
        display: flex;
        flex-wrap: wrap;
    }

    .image-container {
        position: relative;
        margin-right: 10px;
        margin-bottom: 10px;
    }

    .image-container img {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
        transition: transform 0.3s;
    }

    .image-container img:hover {
        transform: scale(1.05); /* Slight zoom effect on hover */
    }

    .delete-btn {
        position: absolute;
        top: 5px;
        right: 5px;
        background: none;
        color: white;
        font-size: 18px;
        padding: 5px;
        cursor: pointer;
        text-decoration: none;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6); /* Adds a shadow */
    }

    .delete-btn:hover {
        color:red;
        background:none;
    }

    .btn-danger{
        background-color: #f44336 !important;
    }

        .btn-danger:hover{
        background-color:rgb(187, 31, 20) !important;
    }

    .btn {
        background-color: #00bcd4; /* Modern cyan for buttons */
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s, transform 0.3s;
    }

    .btn:hover {
        background-color:rgb(7, 161, 161); /* Darker cyan on hover */
        transform: translateY(-2px); /* Slight lift effect */
    }

    .alert {
        margin-top: 20px;
        padding: 15px;
        border-radius: 5px;
    }

    .alert-success {
        background-color: #4caf50;
        color: white;
    }

    .alert-danger {
        background-color: #f44336;
        color: white;
    }

    small.form-text {
        color: #bbbbbb; /* Slightly lighter gray for helper text */
    }
    </style>
</head>
<body>
    <button class="toggle-btn" id="menuToggle"><i class="fas fa-bars"></i></button>

    <div class="wrapper">
        <?php include '../includes/sidebar.php'; ?>

        <div class="main-content">
            <h2 class="mb-4 text-accent"><i class="fas fa-user-edit"></i> Edit Profile</h2>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="mb-3">Basic Information</h4>
                        
                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Middle Name</label>
                            <input type="text" name="middle_name" class="form-control" value="<?= htmlspecialchars($user['middle_name'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>">
                        </div>
                        
                        
                        <div class="mb-3">
                            <label class="form-label">Caption</label>
                            <input type="text" name="caption" class="form-control" value="<?= htmlspecialchars($user['caption'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Bio</label>
                            <textarea name="bio" class="form-control" rows="5"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                        </div>
                        
                        <br><div class="mb-3">
                            <label class="form-label">Profile Photo</label>
                            <input type="file" name="profile_photo" class="form-control">
                            <?php if (!empty($user['profile_photo'])): ?>
                                <div class="mt-2">
                                    <img src="<?= htmlspecialchars($user['profile_photo']) ?>" alt="Current Profile Photo" style="max-width: 200px;">
                                </div>
                            <?php endif; ?>
                        </div>

                        <br><div class="mb-3">
                        <label class="form-label">Cover Photo</label>
                        <input type="file" name="cover_photo" class="form-control">
                        <?php if (!empty($user['cover_photo'])): ?>
                            <div class="mt-2">
                                <img src="<?= htmlspecialchars($user['cover_photo']) ?>" alt="Current Cover Photo" style="max-width: 100%; max-height: 200px; object-fit: cover;">
                            </div>
                        <?php endif; ?>
                    </div>
                    </div>



                    
                    <div class="col-md-6">
                        <h4 class="mb-3">Contact Information</h4>
                        
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone_number" class="form-control" value="<?= htmlspecialchars($user['phone_number'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Street Address</label>
                            <input type="text" name="street_address" class="form-control" value="<?= htmlspecialchars($user['street_address'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($user['city'] ?? '') ?>">
                        </div>
                        
                        <h4 class="mb-3 mt-4">Personal Information</h4>
                        
                        <div class="mb-3">
                            <label class="form-label">Birth Date</label>
                            <input type="date" name="birth_date" class="form-control" value="<?= htmlspecialchars($user['birth_date'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Age</label>
                            <input type="number" name="age" class="form-control" value="<?= htmlspecialchars($user['age'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Place of Birth</label>
                            <input type="text" name="birth_place" class="form-control" value="<?= htmlspecialchars($user['birth_place'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nationality</label>
                            <input type="text" name="nationality" class="form-control" value="<?= htmlspecialchars($user['nationality'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Religion</label>
                            <input type="text" name="religion" class="form-control" value="<?= htmlspecialchars($user['religion'] ?? '') ?>">
                        </div>
                    </div>
                </div>


                <br><div class="mb-3">
                        <label class="form-label">Resume (PDF)</label>
                        <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx">
                        <?php if (!empty($user['resume_path'])): ?>
                            <div class="mt-2 d-flex align-items-center gap-2">
                                <a href="<?= htmlspecialchars($user['resume_path']) ?>" target="_blank" class="btn btn-sm btn-primary">
                                    <i class="fas fa-file-pdf"></i> View Current
                                </a>
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteResumeModal">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                
                <br><div class="mb-3">
                    <label class="form-label">Gallery Images (Multiple Upload)</label>
                    <input type="file" name="gallery_images[]" class="form-control" multiple>
                    <small>Upload multiple images for your profile gallery</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Current Gallery Images</label>
                    <div class="image-preview">
                        <?php foreach ($galleryImages as $image): ?>
                            <div class="image-container">
                                <img src="<?= htmlspecialchars($image['image_path']) ?>" alt="Gallery Image">
                                <a href="?delete_image_id=<?= $image['id'] ?>" class="delete-btn" title="Delete Image">&times;</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save Changes</button>
            </form>
        </div>
    </div>

    <!-- Delete Resume Confirmation Modal -->
<div class="modal fade" id="deleteResumeModal" tabindex="-1" aria-labelledby="deleteResumeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-dark">
                <h5 class="modal-title" id="deleteResumeModalLabel">Confirm Resume Deletion</h5>
                <button type="button" class="btn-close" data-bs-close="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-dark">
                Are you sure you want to delete your resume? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="?delete_resume=true" class="btn btn-danger">Delete Resume</a>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');

        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    </script>
</body>
</html>