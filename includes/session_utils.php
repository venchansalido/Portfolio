<?php
function checkSessionExpiration() {
    $inactiveTimeout = 600; // 10 minutes in seconds
    
    // Check if session is set to expire
    if (isset($_SESSION['last_activity'])) {
        $sessionLife = time() - $_SESSION['last_activity'];
        if ($sessionLife > $inactiveTimeout) {
            // Session expired
            session_unset();
            session_destroy();
            return false;
        }
    } else {
        // No last activity time set
        return false;
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
    return true;
}
?>