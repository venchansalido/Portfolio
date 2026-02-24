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

// Fetch existing data
$query = "SELECT * FROM home ORDER BY id DESC LIMIT 1";
$result = $conn->query($query);
$home = $result->fetch_assoc();

// Handle form submission for content update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $greetings = $_POST['greetings'];
    $facebook = $_POST['facebook_link'];
    $instagram = $_POST['instagram_link'];
    $youtube = $_POST['youtube_link'];
    $typing_text = $_POST['typing_text'];

    if ($home) {
        // Update the homepage content
        $update = $conn->prepare("UPDATE home SET greetings=?, facebook_link=?, instagram_link=?, youtube_link=?, typing_text=? WHERE id=?");
        $update->bind_param("sssssi", $greetings, $facebook, $instagram, $youtube, $typing_text, $home['id']);
        $update->execute();
        $success = "Homepage content updated successfully!";
    } else {
        // Insert new homepage content
        $insert = $conn->prepare("INSERT INTO home (greetings, facebook_link, instagram_link, youtube_link, typing_text) VALUES (?, ?, ?, ?, ?)");
        $insert->bind_param("sssss", $greetings, $facebook, $instagram, $youtube, $typing_text);
        $insert->execute();
        $success = "Homepage content created!";
    }

    // Fetch the new data to update the home array
    $result = $conn->query("SELECT * FROM home ORDER BY id DESC LIMIT 1");
    $home = $result->fetch_assoc();

    // Handle multiple image uploads
    if (isset($_FILES['hero_images']) && count($_FILES['hero_images']['name']) > 0) {
        // Loop through each uploaded file
        for ($i = 0; $i < count($_FILES['hero_images']['name']); $i++) {
            if ($_FILES['hero_images']['error'][$i] == 0) {
                $imageTmp = $_FILES['hero_images']['tmp_name'][$i];
                $imageName = basename($_FILES['hero_images']['name'][$i]);
                $imagePath = "../assets/images/" . $imageName;

                // Move the uploaded image to assets folder
                if (move_uploaded_file($imageTmp, $imagePath)) {
                    // Store image path in the database
                    $stmt = $conn->prepare("INSERT INTO home_images (image_path) VALUES (?)");
                    $stmt->bind_param("s", $imagePath);
                    $stmt->execute();
                } else {
                    $error = "Image upload failed!";
                }
            }
        }
        $success .= " Images uploaded and saved successfully!";
    }
}

// Handle image deletion
if (isset($_GET['delete_image_id'])) {
    $imageId = $_GET['delete_image_id'];

    // Fetch the image path from the database
    $query = "SELECT image_path FROM home_images WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $imageId);
    $stmt->execute();
    $stmt->bind_result($imagePath);
    $stmt->fetch();
    $stmt->close();

    // Delete image file from the folder
    if (file_exists($imagePath)) {
        unlink($imagePath); // Remove the file from the server
    }

    // Delete the image entry from the database
    $deleteQuery = "DELETE FROM home_images WHERE id = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bind_param("i", $imageId);
    $deleteStmt->execute();

    $success = "Image deleted successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Homepage</title>
    <!-- Bootstrap + Fonts + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
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
        background-color: #008c8c; /* Darker cyan on hover */
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
            <h2 class="mb-4"><i class="fas fa-edit"></i> Edit Home Page Content</h2>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Greetings Text</label>
                    <textarea name="greetings" class="form-control" rows="2"><?= htmlspecialchars($home['greetings'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="typing_text" class="form-label">Typing Text (comma separated)</label>
                    <textarea name="typing_text" id="typing_text" class="form-control" rows="3"><?= htmlspecialchars($home['typing_text'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Facebook Link</label>
                    <input type="text" name="facebook_link" class="form-control" value="<?= htmlspecialchars($home['facebook_link'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Instagram Link</label>
                    <input type="text" name="instagram_link" class="form-control" value="<?= htmlspecialchars($home['instagram_link'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">LinkedIn Link</label>
                    <input type="text" name="youtube_link" class="form-control" value="<?= htmlspecialchars($home['youtube_link'] ?? '') ?>">
                </div>

                <!-- Multiple Image Upload -->
                <div class="mb-3">
                    <label class="form-label">Hero Images (Upload Multiple Images)</label>
                    <input type="file" name="hero_images[]" class="form-control" multiple>
                    <small class="form-text text-white">Select multiple images to upload for the homepage hero section.</small>
                </div>

                <!-- Display Uploaded Images -->
                <div class="mb-3">
                    <label class="form-label">Uploaded Hero Images</label>
                    <div class="image-preview">
                        <?php
                        $imageQuery = "SELECT * FROM home_images";
                        $imageResult = $conn->query($imageQuery);
                        while ($image = $imageResult->fetch_assoc()) {
                            echo '<div class="image-container">';
                            echo '<img src="' . htmlspecialchars($image['image_path']) . '" alt="Uploaded Image">';
                            echo '<a href="?delete_image_id=' . $image['id'] . '" class="delete-btn">&times;</a>'; // Use &times; for "X"
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');

        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    </script>
</body>
</html>
