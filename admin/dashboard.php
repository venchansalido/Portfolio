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

$userId = $_SESSION['user_id'] ?? 1;

$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            font-family: 'Poppins', sans-serif;
        }

        .cover-photo {
            height: 500px;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .profile-img-wrapper {
            position: absolute;
            bottom: -60px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
        }

        .profile-img-wrapper img {
            width: 220px;
            height: 220px;
            border-radius: 50%;
            border: 4px solid #121212;
            object-fit: cover;
        }

        .profile-content {
            padding-top: 80px;
        }

        .info-box {
            background-color: #1e1e1e;
            padding: 20px;
            border-radius: 10px;
        }

        .info-box h5 {
            color: #00bcd4;
            font-weight: 600;
        }

        .info-box ul {
            padding-left: 0;
            list-style: none;
        }

        .info-box ul li {
            margin-bottom: 10px;
        }

        .toggle-btn {
            background: none;
            color: white;
            border: none;
            font-size: 24px;
            position: absolute;
            top: 10px;
            left: 10px;
        }

        .wrapper {
            display: flex;
        }

        .main-content {
            flex-grow: 1;
            padding: 20px;
        }

        .caption{
            color:beige;
        }
    </style>
</head>
<body>
    <button class="toggle-btn" id="menuToggle"><i class="fas fa-bars"></i></button>

    <div class="wrapper">
        <?php include '../includes/sidebar.php'; ?>

        <div class="main-content">
            <!-- Cover Photo -->
            <div class="cover-photo mb-5" style="background-image: url('<?= htmlspecialchars($user['cover_photo'] ?? '../assets/images/default-cover.jpg') ?>');">
                <div class="profile-img-wrapper">
                    <img src="<?= htmlspecialchars($user['profile_photo'] ?? '../assets/images/default-avatar.png') ?>" alt="Profile Photo">
                </div>
            </div>

            <!-- Profile Content -->
            <div class="profile-content text-center mb-4">
                <h2><?= htmlspecialchars(trim($user['first_name'] . ' ' . $user['middle_name'] . ' ' . $user['last_name'])) ?></h2>
                <p class="caption"><?= htmlspecialchars($user['caption'] ?? '') ?></p>
            </div>


            <!-- About and Personal Info -->
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="info-box">
                        <h5><i class="fas fa-user"></i> About</h5>
                        <p><?= nl2br(htmlspecialchars($user['bio'] ?? 'No bio provided.')) ?></p>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="info-box">
                        <h5><i class="fas fa-id-card"></i> Personal Info</h5>
                        <ul>
                            <li><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></li>
                            <li><strong>Phone:</strong> <?= htmlspecialchars($user['phone_number']) ?></li>
                            <li><strong>Address:</strong> <?= htmlspecialchars($user['street_address'] . ', ' . $user['city']) ?></li>
                            <li><strong>Birth Date:</strong> <?= htmlspecialchars($user['birth_date']) ?></li>
                            <li><strong>Age:</strong> <?= htmlspecialchars($user['age']) ?></li>
                            <li><strong>Birth Place:</strong> <?= htmlspecialchars($user['birth_place']) ?></li>
                            <li><strong>Nationality:</strong> <?= htmlspecialchars($user['nationality']) ?></li>
                            <li><strong>Religion:</strong> <?= htmlspecialchars($user['religion']) ?></li>
                        </ul>
                    </div>
                </div>
            </div>
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
