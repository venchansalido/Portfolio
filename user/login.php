<?php
session_start();
include '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $username = htmlspecialchars($username);
    $password = htmlspecialchars($password);

    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $hashed_password, $role);

    if ($stmt->num_rows > 0) {
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            // Set session expiration to 10 minutes (600 seconds)
            $_SESSION['user_id'] = $user_id;
            $_SESSION['role'] = $role;
            $_SESSION['login_success'] = true;
            $_SESSION['last_activity'] = time(); // Record last activity time
            
            // Set session cookie lifetime (optional but recommended)
            $sessionLifetime = 600; // 10 minutes in seconds
            session_set_cookie_params($sessionLifetime);
            
            // Return success response
            echo json_encode(['success' => true]);
            exit;
        }
    }

    // Return error response
    echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    exit;
}
?>