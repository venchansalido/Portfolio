<?php
session_start();
include '../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all fields']);
    exit;
}

$stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    exit;
}

$stmt->bind_result($user_id, $hashed_password, $role);
$stmt->fetch();

if (!password_verify($password, $hashed_password)) {
    echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    exit;
}

// Login successful
session_regenerate_id(true);
$_SESSION['user_id']       = $user_id;
$_SESSION['role']          = $role;
$_SESSION['login_success'] = true;
$_SESSION['last_activity'] = time();

$sessionLifetime = 600; // 10 minutes
session_set_cookie_params($sessionLifetime);

// Decide redirect based on role
$redirect = ($role === 'admin') ? '/venard/admin/dashboard.php' : '/venard/index.php';

echo json_encode([
    'success'  => true,
    'redirect' => $redirect
]);
exit;
?>