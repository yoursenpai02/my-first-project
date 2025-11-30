<?php
/**
 * Digital Tribal Heritage (DTH) - User Logout
 * Handles user session termination
 */

require_once __DIR__ . '/../../config/config.php';

// Log user action before destroying session
if (isLoggedIn()) {
    logUserAction('User logout', "User {$_SESSION['user_name']} logged out");
}

// Destroy all session data
session_destroy();

// Clear remember me cookies
setcookie('remember_selector', '', time() - 3600, '/');
setcookie('remember_token', '', time() - 3600, '/');

// Redirect to login page with success message
$_SESSION['success'] = 'You have been logged out successfully.';
header('Location: ' . APP_URL . 'includes/pages/auth/login.php');
exit();
?>