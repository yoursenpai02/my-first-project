<?php
/**
 * Digital Tribal Heritage (DTH) - Global Configuration
 * Application settings and constants
 */

// Start session
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'dth_database');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application settings
define('APP_NAME', 'Digital Tribal Heritage');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/dth/');

// Security settings
define('HASH_COST', 12); // For password_hash
define('SESSION_LIFETIME', 7200); // 2 hours
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutes

// File upload settings
define('UPLOAD_MAX_SIZE', 2097152); // 2MB
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('UPLOAD_PATH', __DIR__ . '/../assets/images/uploads/');

// Pagination settings
define('ITEMS_PER_PAGE', 12);
define('BLOGS_PER_PAGE', 10);
define('REVIEWS_PER_PAGE', 5);

// Email configuration (for production)
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('EMAIL_FROM', 'noreply@dth.com');

// Debug settings
define('DEBUG_MODE', true);
define('ERROR_LOG_FILE', __DIR__ . '/../logs/error.log');

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Error reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', ERROR_LOG_FILE);
}

// User roles
define('ROLE_TOURIST', 'tourist');
define('ROLE_TRIBAL', 'tribal');
define('ROLE_ADMIN', 'admin');

// Booking statuses
define('BOOKING_PENDING', 'pending');
define('BOOKING_ACCEPTED', 'accepted');
define('BOOKING_REJECTED', 'rejected');
define('BOOKING_COMPLETED', 'completed');
define('BOOKING_CANCELLED', 'cancelled');

// Content statuses
define('STATUS_PENDING', 'pending');
define('STATUS_APPROVED', 'approved');
define('STATUS_REJECTED', 'rejected');

// Place types
$PLACE_TYPES = [
    'waterfall' => 'Waterfall',
    'hill' => 'Hill/Mountain',
    'temple' => 'Temple',
    'forest' => 'Forest',
    'lake' => 'Lake',
    'fort' => 'Fort',
    'park' => 'Park',
    'other' => 'Other'
];

// Service types
$SERVICE_TYPES = [
    'hotel' => 'Hotel',
    'homestay' => 'Homestay',
    'hospital' => 'Hospital',
    'police_station' => 'Police Station',
    'atm' => 'ATM',
    'restaurant' => 'Restaurant',
    'taxi' => 'Taxi Service',
    'other' => 'Other'
];

// Art categories
$ART_CATEGORIES = [
    'painting' => 'Painting',
    'jewelry' => 'Jewelry',
    'craft' => 'Craft',
    'textile' => 'Textile',
    'sculpture' => 'Sculpture',
    'other' => 'Other'
];

// Blog categories
$BLOG_CATEGORIES = [
    'travel' => 'Travel',
    'culture' => 'Culture',
    'food' => 'Food',
    'story' => 'Story',
    'news' => 'News',
    'other' => 'Other'
];

// Jharkhand districts
$JHARKHAND_DISTRICTS = [
    'Ranchi' => 'Ranchi',
    'Jamshedpur' => 'Jamshedpur (East Singhbhum)',
    'Dhanbad' => 'Dhanbad',
    'Bokaro' => 'Bokaro',
    'Giridih' => 'Giridih',
    'Hazaribagh' => 'Hazaribagh',
    'Deoghar' => 'Deoghar',
    'Palamu' => 'Palamu',
    'Latehar' => 'Latehar',
    'Lohardaga' => 'Lohardaga',
    'Godda' => 'Godda',
    'Sahebganj' => 'Sahebganj',
    'Pakur' => 'Pakur',
    'Dumka' => 'Dumka',
    'Khunti' => 'Khunti',
    'Ramgarh' => 'Ramgarh',
    'Saraikela' => 'Saraikela',
    'Simdega' => 'Simdega',
    'West Singhbhum' => 'West Singhbhum',
    'Chatra' => 'Chatra',
    'Koderma' => 'Koderma',
    'Gumla' => 'Gumla'
];

// Default user images
define('DEFAULT_USER_IMAGE', 'assets/images/placeholder/user-placeholder.png');
define('DEFAULT_PLACE_IMAGE', 'assets/images/placeholder/place-placeholder.png');
define('DEFAULT_HOMESTAY_IMAGE', 'assets/images/placeholder/homestay-placeholder.png');
define('DEFAULT_GUIDE_IMAGE', 'assets/images/placeholder/guide-placeholder.png');
define('DEFAULT_ART_IMAGE', 'assets/images/placeholder/art-placeholder.png');
define('DEFAULT_STORY_IMAGE', 'assets/images/placeholder/story-placeholder.png');

// Navigation menu items
function getNavigationMenu($userRole = null) {
    $publicMenu = [
        ['name' => 'Home', 'url' => 'index.php', 'icon' => 'home'],
        ['name' => 'Places', 'url' => 'places.php', 'icon' => 'map-marker'],
        ['name' => 'Homestays', 'url' => 'homestays.php', 'icon' => 'home'],
        ['name' => 'Guides', 'url' => 'guides.php', 'icon' => 'user-tie'],
        ['name' => 'Art & Culture', 'url' => 'art-gallery.php', 'icon' => 'palette'],
        ['name' => 'Stories', 'url' => 'stories.php', 'icon' => 'book'],
        ['name' => 'Blogs', 'url' => 'blogs.php', 'icon' => 'newspaper'],
        ['name' => 'About', 'url' => 'about.php', 'icon' => 'info-circle'],
        ['name' => 'Contact', 'url' => 'contact.php', 'icon' => 'envelope']
    ];

    if ($userRole) {
        $userMenu = [
            ['name' => 'Dashboard', 'url' => 'dashboard/index.php', 'icon' => 'dashboard'],
            ['name' => 'My Bookings', 'url' => 'dashboard/bookings.php', 'icon' => 'calendar'],
            ['name' => 'Profile', 'url' => 'auth/profile.php', 'icon' => 'user'],
            ['name' => 'Logout', 'url' => 'auth/logout.php', 'icon' => 'sign-out-alt']
        ];

        $tribalMenu = [
            ['name' => 'My Homestays', 'url' => 'dashboard/manage-homestay.php', 'icon' => 'home'],
            ['name' => 'My Guides', 'url' => 'dashboard/manage-guides.php', 'icon' => 'user-tie'],
            ['name' => 'My Art', 'url' => 'dashboard/manage-art.php', 'icon' => 'palette'],
            ['name' => 'My Blogs', 'url' => 'dashboard/manage-blogs.php', 'icon' => 'newspaper']
        ];

        $adminMenu = [
            ['name' => 'Admin Panel', 'url' => 'admin/index.php', 'icon' => 'shield-alt'],
            ['name' => 'Users', 'url' => 'admin/users.php', 'icon' => 'users'],
            ['name' => 'Places', 'url' => 'admin/places.php', 'icon' => 'map-marker'],
            ['name' => 'Approvals', 'url' => 'admin/', 'icon' => 'check-circle']
        ];

        if ($userRole === ROLE_ADMIN) {
            return array_merge($publicMenu, $userMenu, $adminMenu);
        } elseif ($userRole === ROLE_TRIBAL) {
            return array_merge($publicMenu, $userMenu, $tribalMenu);
        } else {
            return array_merge($publicMenu, $userMenu);
        }
    }

    return array_merge($publicMenu, [
        ['name' => 'Login', 'url' => 'auth/login.php', 'icon' => 'sign-in-alt'],
        ['name' => 'Register', 'url' => 'auth/register.php', 'icon' => 'user-plus']
    ]);
}

// Utility functions
function formatCurrency($amount) {
    return '₹' . number_format($amount, 2);
}

function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    return preg_match('/^[6-9]\d{9}$/', $phone);
}

function isUserLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUserName() {
    return $_SESSION['user_name'] ?? 'Guest';
}

// Include the database connection
require_once __DIR__ . '/database.php';
?>