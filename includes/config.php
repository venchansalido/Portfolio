<?php
// Strict error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Never show errors to users
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Check if the file is being accessed directly
if (basename($_SERVER['PHP_SELF']) == 'config.php') {
    header('HTTP/1.1 403 Forbidden');
    die('Direct access not permitted');
}

// Environment variable configuration with fallbacks
$host = getenv('DB_HOST') ?: 'sql302.infinityfree.com';
$user = getenv('DB_USER') ?: 'if0_38959668';
$pass = getenv('DB_PASS') ?: '3KUXsLHPee6qPHM';
$dbname = getenv('DB_NAME') ?: 'if0_38959668_venard';

// Validate environment variables in production
if ($_SERVER['SERVER_NAME'] === 'pesozambo.xyz') {
    if (empty($host) || empty($user) || empty($dbname)) {
        error_log('Database configuration incomplete');
        die('System configuration error. Please contact administrator.');
    }
}

// Database connection with enhanced security
try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed");
    }

    // Set secure connection parameters
    $conn->set_charset("utf8mb4");
    $conn->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, true);
    $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5); // 5 second timeout
    
    // Enable SSL if available
    if (defined('MYSQLI_CLIENT_SSL') && file_exists('/path/to/cert.pem')) {
        $conn->ssl_set(
            '/path/to/client-key.pem',
            '/path/to/client-cert.pem',
            '/path/to/ca-cert.pem',
            null,
            null
        );
    }

    // Enable strict reporting in development
    if ($_SERVER['SERVER_NAME'] !== 'pesozambo.xyz') {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    }

} catch (Exception $e) {
    // Secure error handling
    error_log('Database error: ' . $e->getMessage());
    
    // Generic error message for production
    $errorMessage = ($_SERVER['SERVER_NAME'] === 'pesozambo.xyz')
        ? 'Database connection error. Please try again later.'
        : 'Database error: ' . $e->getMessage();
    
    die($errorMessage);
}

// Additional security measures
register_shutdown_function(function() use ($conn) {
    if ($conn instanceof mysqli) {
        $conn->close();
    }
});

// Prevent information leakage
header_remove('X-Powered-By');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
?>