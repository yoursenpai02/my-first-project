<?php
/**
 * Digital Tribal Heritage (DTH) - Utility Functions
 * Common helper functions for the application
 */

require_once __DIR__ . '/config.php';

/**
 * Security Functions
 */

// Generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Sanitize input data
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Validate and sanitize file upload
function validateFileUpload($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'], $maxSize = 2097152) {
    $errors = [];

    // Check if file was uploaded
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        $errors[] = 'No file uploaded or upload error occurred.';
        return $errors;
    }

    // Check file size
    if ($file['size'] > $maxSize) {
        $errors[] = 'File size exceeds maximum allowed size of ' . ($maxSize / 1024 / 1024) . 'MB.';
    }

    // Check file type
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($fileInfo, $file['tmp_name']);
    finfo_close($fileInfo);

    $allowedMimeTypes = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/gif' => ['gif']
    ];

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedTypes) || !isset($allowedMimeTypes[$mimeType])) {
        $errors[] = 'Invalid file type. Only ' . implode(', ', $allowedTypes) . ' are allowed.';
    }

    return $errors;
}

// Generate unique filename
function generateUniqueFilename($originalName) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    return uniqid() . '_' . time() . '.' . $extension;
}

// Upload file to specified directory
function uploadFile($file, $targetDir, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'], $maxSize = 2097152) {
    $errors = validateFileUpload($file, $allowedTypes, $maxSize);

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    // Create directory if it doesn't exist
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // Generate unique filename
    $filename = generateUniqueFilename($file['name']);
    $targetPath = $targetDir . '/' . $filename;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $filename, 'path' => $targetPath];
    } else {
        return ['success' => false, 'errors' => ['Failed to move uploaded file.']];
    }
}

/**
 * Authentication Functions
 */

// Hash password securely
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT, ['cost' => HASH_COST]);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Require user to be logged in
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = 'Please login to access this page.';
        header('Location: ' . APP_URL . 'auth/login.php');
        exit();
    }
}

// Check user role
function hasRole($role) {
    return isLoggedIn() && $_SESSION['user_role'] === $role;
}

// Require specific role
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        $_SESSION['error'] = 'You do not have permission to access this page.';
        header('Location: ' . APP_URL . 'index.php');
        exit();
    }
}

// Check if user can access content
function canAccessContent($contentType, $contentId = null) {
    if (!isLoggedIn()) {
        return false;
    }

    $userRole = $_SESSION['user_role'];
    $userId = $_SESSION['user_id'];

    // Admin can access everything
    if ($userRole === ROLE_ADMIN) {
        return true;
    }

    // For now, allow basic access for tourists and tribal users
    // Content-specific checks can be added here
    return true;
}

/**
 * Database Helper Functions
 */

// Execute prepared statement with error handling
function executeStatement($db, $sql, $params = []) {
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        if (DEBUG_MODE) {
            throw $e;
        }
        return false;
    }
}

// Get single record
function fetchOne($db, $sql, $params = []) {
    $stmt = executeStatement($db, $sql, $params);
    return $stmt ? $stmt->fetch() : false;
}

// Get multiple records
function fetchAll($db, $sql, $params = []) {
    $stmt = executeStatement($db, $sql, $params);
    return $stmt ? $stmt->fetchAll() : [];
}

// Insert record and return ID
function insertRecord($db, $sql, $params = []) {
    $stmt = executeStatement($db, $sql, $params);
    return $stmt ? $db->lastInsertId() : false;
}

// Update record
function updateRecord($db, $sql, $params = []) {
    $stmt = executeStatement($db, $sql, $params);
    return $stmt ? $stmt->rowCount() : false;
}

// Delete record
function deleteRecord($db, $sql, $params = []) {
    $stmt = executeStatement($db, $sql, $params);
    return $stmt ? $stmt->rowCount() : false;
}

// Check if record exists
function recordExists($db, $table, $condition, $params = []) {
    $sql = "SELECT COUNT(*) as count FROM $table WHERE $condition";
    $result = fetchOne($db, $sql, $params);
    return $result && $result['count'] > 0;
}

/**
 * Validation Functions
 */

// Validate email format
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Validate Indian phone number
function isValidPhone($phone) {
    return preg_match('/^[6-9]\d{9}$/', $phone);
}

// Validate password strength
function isValidPassword($password) {
    return strlen($password) >= 8 && preg_match('/[A-Za-z]/', $password) && preg_match('/\d/', $password);
}

// Validate name (letters and spaces only)
function isValidName($name) {
    return preg_match('/^[A-Za-z\s]{2,100}$/', $name);
}

// Validate rating (1-5)
function isValidRating($rating) {
    return is_numeric($rating) && $rating >= 1 && $rating <= 5;
}

// Validate price
function isValidPrice($price) {
    return is_numeric($price) && $price > 0;
}

// Validate date
function isValidDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Validate future date
function isFutureDate($date) {
    return strtotime($date) > strtotime('today');
}

/**
 * Form Processing Functions
 */

// Get POST value safely
function post($key, $default = '') {
    return sanitize($_POST[$key] ?? $default);
}

// Get GET value safely
function get($key, $default = '') {
    return sanitize($_GET[$key] ?? $default);
}

// Check if form was submitted
function isPost() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

// Redirect with message
function redirect($url, $message = '', $type = 'success') {
    if (!empty($message)) {
        $_SESSION[$type] = $message;
    }
    header("Location: $url");
    exit();
}

// Display success/error messages
function displayMessage() {
    $output = '';

    if (isset($_SESSION['success'])) {
        $output .= '<div class="alert alert-success alert-dismissible fade show" role="alert">';
        $output .= $_SESSION['success'];
        $output .= '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        $output .= '</div>';
        unset($_SESSION['success']);
    }

    if (isset($_SESSION['error'])) {
        $output .= '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
        $output .= $_SESSION['error'];
        $output .= '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        $output .= '</div>';
        unset($_SESSION['error']);
    }

    if (isset($_SESSION['info'])) {
        $output .= '<div class="alert alert-info alert-dismissible fade show" role="alert">';
        $output .= $_SESSION['info'];
        $output .= '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        $output .= '</div>';
        unset($_SESSION['info']);
    }

    echo $output;
}

/**
 * Pagination Functions
 */

// Generate pagination links
function generatePagination($currentPage, $totalPages, $baseUrl, $pageParam = 'page') {
    if ($totalPages <= 1) {
        return '';
    }

    $html = '<nav aria-label="Page navigation">';
    $html .= '<ul class="pagination justify-content-center">';

    // Previous button
    if ($currentPage > 1) {
        $prevPage = $currentPage - 1;
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . $baseUrl . '?' . $pageParam . '=' . $prevPage . '">Previous</a>';
        $html .= '</li>';
    } else {
        $html .= '<li class="page-item disabled">';
        $html .= '<span class="page-link">Previous</span>';
        $html .= '</li>';
    }

    // Page numbers
    $startPage = max(1, $currentPage - 2);
    $endPage = min($totalPages, $currentPage + 2);

    if ($startPage > 1) {
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . $baseUrl . '?' . $pageParam . '=1">1</a>';
        $html .= '</li>';

        if ($startPage > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }

    for ($i = $startPage; $i <= $endPage; $i++) {
        if ($i == $currentPage) {
            $html .= '<li class="page-item active">';
            $html .= '<span class="page-link">' . $i . '</span>';
            $html .= '</li>';
        } else {
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . $baseUrl . '?' . $pageParam . '=' . $i . '">' . $i . '</a>';
            $html .= '</li>';
        }
    }

    if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }

        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . $baseUrl . '?' . $pageParam . '=' . $totalPages . '">' . $totalPages . '</a>';
        $html .= '</li>';
    }

    // Next button
    if ($currentPage < $totalPages) {
        $nextPage = $currentPage + 1;
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . $baseUrl . '?' . $pageParam . '=' . $nextPage . '">Next</a>';
        $html .= '</li>';
    } else {
        $html .= '<li class="page-item disabled">';
        $html .= '<span class="page-link">Next</span>';
        $html .= '</li>';
    }

    $html .= '</ul>';
    $html .= '</nav>';

    return $html;
}

/**
 * Search and Filter Functions
 */

// Build WHERE clause for filters
function buildWhereClause($filters = []) {
    $conditions = [];
    $params = [];

    foreach ($filters as $field => $value) {
        if (!empty($value)) {
            if (is_array($value)) {
                // IN clause
                $placeholders = str_repeat('?,', count($value) - 1) . '?';
                $conditions[] = "$field IN ($placeholders)";
                $params = array_merge($params, $value);
            } elseif (strpos($value, '%') !== false) {
                // LIKE clause
                $conditions[] = "$field LIKE ?";
                $params[] = $value;
            } else {
                // Equals clause
                $conditions[] = "$field = ?";
                $params[] = $value;
            }
        }
    }

    $whereClause = '';
    if (!empty($conditions)) {
        $whereClause = 'WHERE ' . implode(' AND ', $conditions);
    }

    return ['clause' => $whereClause, 'params' => $params];
}

/**
 * Email Functions (basic implementation)
 */

// Send email using mail() function (for development)
function sendEmail($to, $subject, $message, $headers = '') {
    $defaultHeaders = "From: " . EMAIL_FROM . "\r\n";
    $defaultHeaders .= "Content-Type: text/html; charset=UTF-8\r\n";

    $headers = $defaultHeaders . $headers;

    return mail($to, $subject, $message, $headers);
}

/**
 * Logging Functions
 */

// Log user action
function logUserAction($action, $details = '') {
    if (isLoggedIn()) {
        $logMessage = sprintf(
            "[%s] User %s (ID: %d) - %s - %s\n",
            date('Y-m-d H:i:s'),
            $_SESSION['user_name'],
            $_SESSION['user_id'],
            $action,
            $details
        );

        error_log($logMessage, 3, ERROR_LOG_FILE);
    }
}

// Log admin action to database
function logAdminAction($db, $actionType, $targetType = null, $targetId = null, $details = null) {
    if (hasRole(ROLE_ADMIN)) {
        $sql = "INSERT INTO admin_logs (admin_id, action_type, target_type, target_id, details) VALUES (?, ?, ?, ?, ?)";
        $params = [
            $_SESSION['user_id'],
            $actionType,
            $targetType,
            $targetId,
            $details
        ];

        return insertRecord($db, $sql, $params);
    }

    return false;
}

/**
 * Image Processing Functions
 */

// Create thumbnail from image
function createThumbnail($sourcePath, $destPath, $width = 300, $height = 300) {
    $imageInfo = getimagesize($sourcePath);

    if (!$imageInfo) {
        return false;
    }

    list($sourceWidth, $sourceHeight, $imageType) = $imageInfo;

    // Calculate dimensions while maintaining aspect ratio
    $ratio = min($width / $sourceWidth, $height / $sourceHeight);
    $newWidth = (int)($sourceWidth * $ratio);
    $newHeight = (int)($sourceHeight * $ratio);

    // Create image resource based on type
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($sourcePath);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($sourcePath);
            break;
        default:
            return false;
    }

    if (!$source) {
        return false;
    }

    // Create thumbnail
    $thumbnail = imagecreatetruecolor($newWidth, $newHeight);

    // Preserve transparency for PNG and GIF
    if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
        imagecolortransparent($thumbnail, imagecolorallocatealpha($thumbnail, 0, 0, 0, 127));
        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);
    }

    imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);

    // Save thumbnail
    $success = false;
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $success = imagejpeg($thumbnail, $destPath, 90);
            break;
        case IMAGETYPE_PNG:
            $success = imagepng($thumbnail, $destPath, 9);
            break;
        case IMAGETYPE_GIF:
            $success = imagegif($thumbnail, $destPath);
            break;
    }

    // Clean up
    imagedestroy($source);
    imagedestroy($thumbnail);

    return $success;
}

/**
 * Cache Functions (simple file-based caching)
 */

// Get cached data
function getCache($key, $expiration = 3600) {
    $cacheFile = __DIR__ . '/../cache/' . md5($key) . '.cache';

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $expiration) {
        return unserialize(file_get_contents($cacheFile));
    }

    return false;
}

// Set cached data
function setCache($key, $data) {
    $cacheDir = __DIR__ . '/../cache';

    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }

    $cacheFile = $cacheDir . '/' . md5($key) . '.cache';
    return file_put_contents($cacheFile, serialize($data)) !== false;
}

// Clear cache
function clearCache($pattern = '*') {
    $cacheFiles = glob(__DIR__ . '/../cache/' . $pattern . '.cache');

    foreach ($cacheFiles as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }

    return true;
}
?>