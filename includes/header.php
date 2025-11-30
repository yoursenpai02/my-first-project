<?php
/**
 * Digital Tribal Heritage (DTH) - HTML Header
 * Common header component with navigation and branding
 */

// Get current page filename for active state highlighting
$currentFile = basename($_SERVER['PHP_SELF']);
$userRole = getCurrentUserRole();
$isLoggedIn = isLoggedIn();
$navigationMenu = getNavigationMenu($userRole);

// Set page title
$pageTitle = $pageTitle ?? APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription ?? 'Digital Tribal Heritage - Explore Jharkhand tourism, stay with tribal families, experience authentic culture, and support local communities.'); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($metaKeywords ?? 'tribal tourism, Jharkhand, tribal heritage, homestays, tribal culture, tribal art, tourism platform'); ?>">
    <meta name="author" content="<?php echo htmlspecialchars($metaAuthor ?? 'Digital Tribal Heritage'); ?>">
    <meta name="robots" content="index, follow">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($metaDescription ?? 'Digital Tribal Heritage - Explore Jharkhand tourism, stay with tribal families, experience authentic culture, and support local communities.'); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo APP_URL . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:image" content="<?php echo APP_URL; ?>assets/images/placeholder/dth-og-image.jpg">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($metaDescription ?? 'Digital Tribal Heritage - Explore Jharkhand tourism, stay with tribal families, experience authentic culture, and support local communities.'); ?>">
    <meta name="twitter:image" content="<?php echo APP_URL; ?>assets/images/placeholder/dth-og-image.jpg">

    <title><?php echo htmlspecialchars($pageTitle); ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo APP_URL; ?>assets/images/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo APP_URL; ?>assets/images/apple-touch-icon.png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Lato:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>assets/css/responsive.css">

    <!-- Preload Critical Resources -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Lato:wght@300;400;600;700&display=swap" as="style">
    <link rel="preload" href="<?php echo APP_URL; ?>assets/images/placeholder/logo-placeholder.png" as="image">

    <!-- Schema.org Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "<?php echo htmlspecialchars(APP_NAME); ?>",
        "url": "<?php echo APP_URL; ?>",
        "description": "Digital Tribal Heritage - Explore Jharkhand tourism, stay with tribal families, experience authentic culture, and support local communities.",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "<?php echo APP_URL; ?>search?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>
</head>
<body>
    <!-- Skip to main content for accessibility -->
    <a href="#main-content" class="skip-link visually-hidden-focusable">Skip to main content</a>

    <!-- Header Navigation -->
    <header class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container">
            <!-- Brand Logo -->
            <a class="navbar-brand d-flex align-items-center" href="<?php echo APP_URL; ?>">
                <img src="<?php echo APP_URL; ?>assets/images/placeholder/logo-placeholder.png"
                     alt="<?php echo htmlspecialchars(APP_NAME); ?> Logo"
                     class="me-2"
                     width="40"
                     height="40"
                     loading="lazy">
                <div>
                    <span class="brand-name fw-bold">Digital</span>
                    <span class="brand-name-alt fw-bold">Tribal Heritage</span>
                </div>
            </a>

            <!-- Mobile Toggle Button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navigation Menu -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php foreach ($navigationMenu as $menu): ?>
                        <?php
                        // Check if this is the current page
                        $isActive = (strpos($currentFile, basename($menu['url'])) !== false);

                        // Skip certain menu items based on role or login status
                        if (($menu['name'] === 'Dashboard' || in_array($menu['name'], ['My Bookings', 'My Homestays', 'My Guides', 'My Art', 'My Blogs', 'Admin Panel'])) && !$isLoggedIn) {
                            continue;
                        }
                        ?>

                        <li class="nav-item">
                            <a class="nav-link <?php echo $isActive ? 'active' : ''; ?>"
                               href="<?php echo APP_URL . $menu['url']; ?>"
                               <?php echo $menu['name'] === 'Login' || $menu['name'] === 'Register' ? 'data-bs-toggle="modal" data-bs-target="#authModal"' : ''; ?>>
                                <i class="fas fa-<?php echo htmlspecialchars($menu['icon']); ?> me-1"></i>
                                <span class="d-lg-inline d-none"><?php echo htmlspecialchars($menu['name']); ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <!-- User Account Section -->
                <div class="navbar-nav">
                    <?php if ($isLoggedIn): ?>
                        <!-- User Dropdown -->
                        <div class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#"
                               id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php if (!empty($_SESSION['user_image'])): ?>
                                    <img src="<?php echo APP_URL; ?>assets/images/uploads/thumb_<?php echo htmlspecialchars($_SESSION['user_image']); ?>"
                                         alt="User Profile"
                                         class="rounded-circle me-2"
                                         width="32"
                                         height="32"
                                         loading="lazy">
                                <?php else: ?>
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2"
                                         style="width: 32px; height: 32px;">
                                        <i class="fas fa-user text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                <span class="d-none d-lg-inline">
                                    <?php echo htmlspecialchars(getCurrentUserName()); ?>
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><h6 class="dropdown-header">Account</h6></li>
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>includes/pages/auth/profile.php">
                                    <i class="fas fa-user me-2"></i>My Profile
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>dashboard/index.php">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>dashboard/bookings.php">
                                    <i class="fas fa-calendar me-2"></i>My Bookings
                                </a></li>
                                <?php if ($userRole === ROLE_TRIBAL): ?>
                                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>dashboard/manage-homestay.php">
                                        <i class="fas fa-home me-2"></i>My Homestays
                                    </a></li>
                                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>dashboard/manage-guides.php">
                                        <i class="fas fa-user-tie me-2"></i>My Guides
                                    </a></li>
                                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>dashboard/manage-art.php">
                                        <i class="fas fa-palette me-2"></i>My Art
                                    </a></li>
                                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>dashboard/manage-blogs.php">
                                        <i class="fas fa-newspaper me-2"></i>My Blogs
                                    </a></li>
                                <?php endif; ?>
                                <?php if ($userRole === ROLE_ADMIN): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Admin</h6></li>
                                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>includes/pages/admin/index.php">
                                        <i class="fas fa-shield-alt me-2"></i>Admin Panel
                                    </a></li>
                                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>includes/pages/admin/users.php">
                                        <i class="fas fa-users me-2"></i>Manage Users
                                    </a></li>
                                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>includes/pages/admin/approve-homestays.php">
                                        <i class="fas fa-check-circle me-2"></i>Approve Homestays
                                    </a></li>
                                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>includes/pages/admin/approve-guides.php">
                                        <i class="fas fa-check-circle me-2"></i>Approve Guides
                                    </a></li>
                                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>includes/pages/admin/approve-art.php">
                                        <i class="fas fa-check-circle me-2"></i>Approve Art
                                    </a></li>
                                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>includes/pages/admin/approve-blogs.php">
                                        <i class="fas fa-check-circle me-2"></i>Approve Blogs
                                    </a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?php echo APP_URL; ?>includes/pages/auth/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <!-- Login/Register Buttons -->
                        <a class="btn btn-outline-light me-2" href="<?php echo APP_URL; ?>includes/pages/auth/login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                        <a class="btn btn-light" href="<?php echo APP_URL; ?>includes/pages/auth/register.php">
                            <i class="fas fa-user-plus me-1"></i>Register
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Authentication Modal (for when clicking login/register on public pages) -->
    <?php if (!$isLoggedIn): ?>
        <div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="authModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title" id="authModalLabel">Welcome to Digital Tribal Heritage</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="text-center p-4">
                                    <i class="fas fa-sign-in-alt fa-3x text-primary mb-3"></i>
                                    <h5>Login</h5>
                                    <p class="text-muted mb-4">Access your account and continue your journey</p>
                                    <a href="<?php echo APP_URL; ?>includes/pages/auth/login.php" class="btn btn-primary btn-lg">
                                        Login Now
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-center p-4 bg-light rounded">
                                    <i class="fas fa-user-plus fa-3x text-success mb-3"></i>
                                    <h5>Register</h5>
                                    <p class="text-muted mb-4">Join our community and start exploring</p>
                                    <a href="<?php echo APP_URL; ?>includes/pages/auth/register.php" class="btn btn-success btn-lg">
                                        Create Account
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Success/Error Message Display Area -->
    <div id="message-container" class="fixed-top" style="top: 70px; z-index: 1040; right: 20px; max-width: 400px;">
        <?php displayMessage(); ?>
    </div>

    <!-- Main Content -->
    <main id="main-content" role="main">
        <!-- Breadcrumb (optional) -->
        <?php if (isset($breadcrumb) && !empty($breadcrumb)): ?>
            <nav aria-label="breadcrumb" class="bg-light py-2">
                <div class="container">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="<?php echo APP_URL; ?>">Home</a>
                        </li>
                        <?php foreach ($breadcrumb as $item): ?>
                            <?php if (isset($item['url'])): ?>
                                <li class="breadcrumb-item">
                                    <a href="<?php echo $item['url']; ?>"><?php echo htmlspecialchars($item['text']); ?></a>
                                </li>
                            <?php else: ?>
                                <li class="breadcrumb-item active" aria-current="page">
                                    <?php echo htmlspecialchars($item['text']); ?>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                </div>
            </nav>
        <?php endif; ?>

        <!-- Page Header (optional) -->
        <?php if (isset($pageHeader)): ?>
            <div class="page-header py-4 <?php echo $pageHeader['class'] ?? 'bg-light'; ?>">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col">
                            <h1 class="h2 mb-2"><?php echo htmlspecialchars($pageHeader['title']); ?></h1>
                            <?php if (isset($pageHeader['description'])): ?>
                                <p class="lead text-muted mb-0"><?php echo htmlspecialchars($pageHeader['description']); ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if (isset($pageHeader['actions'])): ?>
                            <div class="col-auto">
                                <?php echo $pageHeader['actions']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>