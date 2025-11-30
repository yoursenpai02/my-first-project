<?php
/**
 * Digital Tribal Heritage (DTH) - Homepage
 * Main landing page showcasing platform features
 */

require_once __DIR__ . '/../../../config/config.php';

// Get featured places, homestays, guides and art items for homepage
$featuredPlaces = fetchAll($db, "SELECT * FROM places ORDER BY created_at DESC LIMIT 6");
$featuredHomestays = fetchAll($db, "SELECT h.*, u.name as host_name, AVG(r.rating) as avg_rating
                                   FROM homestays h
                                   JOIN users u ON h.tribal_user_id = u.id
                                   LEFT JOIN reviews r ON r.related_type = 'homestay' AND r.related_id = h.id
                                   WHERE h.status = 'approved'
                                   GROUP BY h.id
                                   ORDER BY h.created_at DESC
                                   LIMIT 4");
$featuredGuides = fetchAll($db, "SELECT g.*, u.name as guide_name, AVG(r.rating) as avg_rating
                                 FROM guides g
                                 JOIN users u ON g.tribal_user_id = u.id
                                 LEFT JOIN reviews r ON r.related_type = 'guide' AND r.related_id = g.id
                                 WHERE g.status = 'approved'
                                 GROUP BY g.id
                                 ORDER BY g.created_at DESC
                                 LIMIT 4");
$featuredArt = fetchAll($db, "SELECT a.*, u.name as artist_name, AVG(r.rating) as avg_rating
                                FROM art_items a
                                JOIN users u ON a.tribal_user_id = u.id
                                LEFT JOIN reviews r ON r.related_type = 'art' AND r.related_id = a.id
                                WHERE a.status = 'approved' AND a.stock_qty > 0
                                GROUP BY a.id
                                ORDER BY a.created_at DESC
                                LIMIT 4");
$latestBlogs = fetchAll($db, "SELECT b.*, u.name as author_name, u.profile_image as author_image, COUNT(bc.id) as comment_count
                              FROM blogs b
                              JOIN users u ON b.author_id = u.id
                              LEFT JOIN blog_comments bc ON b.id = bc.blog_id
                              WHERE b.status = 'approved'
                              GROUP BY b.id
                              ORDER BY b.created_at DESC
                              LIMIT 3");

// Get platform statistics
$stats = [
    'places' => fetchOne($db, "SELECT COUNT(*) as count FROM places")['count'],
    'homestays' => fetchOne($db, "SELECT COUNT(*) as count FROM homestays WHERE status = 'approved'")['count'],
    'guides' => fetchOne($db, "SELECT COUNT(*) as count FROM guides WHERE status = 'approved'")['count'],
    'art_items' => fetchOne($db, "SELECT COUNT(*) as count FROM art_items WHERE status = 'approved'")['count'],
    'users' => fetchOne($db, "SELECT COUNT(*) as count FROM users WHERE is_active = 1")['count']
];

$pageTitle = 'Home - ' . APP_NAME;
$pageHeader = [
    'title' => 'Discover Jharkhand\'s Tribal Heritage',
    'description' => 'Experience authentic tribal culture, stay with local families, and support traditional art communities.',
    'class' => 'hero tribal-pattern',
    'actions' => '
        <a href="' . APP_URL . 'includes/pages/public/places.php" class="btn btn-light btn-lg me-3">
            <i class="fas fa-compass me-2"></i>Explore Places
        </a>
        <a href="' . APP_URL . 'includes/pages/auth/register.php" class="btn btn-outline-light btn-lg">
            <i class="fas fa-user-plus me-2"></i>Join Community
        </a>'
];

$breadcrumb = [
    ['text' => 'Home', 'active' => true]
];

include __DIR__ . '/../../../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero text-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="hero-content">
                    <h1 class="display-4 fw-bold mb-4 animate__animated animate__fadeInDown">
                        Discover Jharkhand's<br>
                        <span class="text-gradient-primary">Tribal Heritage</span>
                    </h1>
                    <p class="lead mb-5 animate__animated animate__fadeInUp">
                        Connect with authentic tribal communities, explore hidden gems, and support local artisans through responsible tourism.
                    </p>
                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center animate__animated animate__fadeInUp animate__delay-1s">
                        <a href="<?php echo APP_URL; ?>includes/pages/public/places.php" class="btn btn-light btn-lg">
                            <i class="fas fa-map-marked-alt me-2"></i>Explore Places
                        </a>
                        <a href="<?php echo APP_URL; ?>includes/pages/public/homestays.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-home me-2"></i>Book Stays
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-2 col-6 mb-4">
                <div class="stat-item">
                    <div class="stat-icon text-primary mb-3">
                        <i class="fas fa-map-marker-alt fa-2x"></i>
                    </div>
                    <div class="stat-number h2 mb-1" data-target="<?php echo $stats['places']; ?>">0</div>
                    <div class="stat-label text-muted">Tourist Places</div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-4">
                <div class="stat-item">
                    <div class="stat-icon text-success mb-3">
                        <i class="fas fa-home fa-2x"></i>
                    </div>
                    <div class="stat-number h2 mb-1" data-target="<?php echo $stats['homestays']; ?>">0</div>
                    <div class="stat-label text-muted">Homestays</div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-4">
                <div class="stat-item">
                    <div class="stat-icon text-info mb-3">
                        <i class="fas fa-user-tie fa-2x"></i>
                    </div>
                    <div class="stat-number h2 mb-1" data-target="<?php echo $stats['guides']; ?>">0</div>
                    <div class="stat-label text-muted">Local Guides</div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-4">
                <div class="stat-item">
                    <div class="stat-icon text-warning mb-3">
                        <i class="fas fa-palette fa-2x"></i>
                    </div>
                    <div class="stat-number h2 mb-1" data-target="<?php echo $stats['art_items']; ?>">0</div>
                    <div class="stat-label text-muted">Art Items</div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-4">
                <div class="stat-item">
                    <div class="stat-icon text-danger mb-3">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                    <div class="stat-number h2 mb-1" data-target="<?php echo $stats['users']; ?>">0</div>
                    <div class="stat-label text-muted">Community Members</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Places Section -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center mb-5">
            <div class="col-md-8">
                <h2 class="h1 mb-3">
                    <i class="fas fa-compass text-primary me-3"></i>
                    Popular Destinations
                </h2>
                <p class="lead text-muted">
                    Explore Jharkhand's most beautiful tourist places and hidden gems
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="<?php echo APP_URL; ?>includes/pages/public/places.php" class="btn btn-outline-primary">
                    View All Places
                    <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>

        <div class="row g-4">
            <?php foreach ($featuredPlaces as $place): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card place-card h-100">
                        <div class="place-image-container">
                            <?php if (!empty($place['image_path'])): ?>
                                <img src="<?php echo APP_URL; ?>assets/images/placeholder/place-placeholder.png"
                                     alt="<?php echo htmlspecialchars($place['name']); ?>"
                                     class="card-img-top place-image"
                                     loading="lazy">
                            <?php else: ?>
                                <div class="card-img-top place-image-placeholder d-flex align-items-center justify-content-center">
                                    <i class="fas fa-mountain fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div class="place-type-badge">
                                <?php echo htmlspecialchars($PLACE_TYPES[$place['place_type']] ?? $place['place_type']); ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($place['name']); ?></h5>
                            <p class="card-text text-muted mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?php echo htmlspecialchars($place['district']); ?>
                            </p>
                            <p class="card-text"><?php echo truncateText($place['description'], 100); ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-light text-dark">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php echo htmlspecialchars($place['best_time_to_visit']); ?>
                                </span>
                                <a href="<?php echo APP_URL; ?>includes/pages/public/place-detail.php?id=<?php echo $place['id']; ?>"
                                   class="btn btn-primary btn-sm">
                                    Explore
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5 bg-light tribal-pattern">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="h1 mb-3">
                <i class="fas fa-star text-warning me-3"></i>
                Why Choose Digital Tribal Heritage?
            </h2>
            <p class="lead text-muted">
                Experience authentic tribal tourism while supporting local communities
            </p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card h-100">
                    <div class="feature-icon text-primary mb-4">
                        <i class="fas fa-handshake fa-3x"></i>
                    </div>
                    <h5 class="mb-3">Direct Community Support</h5>
                    <p class="text-muted">
                        100% of payments go directly to tribal families, artisans, and local guides. No middlemen, just authentic connections.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card h-100">
                    <div class="feature-icon text-success mb-4">
                        <i class="fas fa-shield-alt fa-3x"></i>
                    </div>
                    <h5 class="mb-3">Safe & Verified</h5>
                    <p class="text-muted">
                        All hosts, guides, and artists are verified by our team. Secure booking system and 24/7 customer support.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card h-100">
                    <div class="feature-icon text-warning mb-4">
                        <i class="fas fa-palette fa-3x"></i>
                    </div>
                    <h5 class="mb-3">Cultural Preservation</h5>
                    <p class="text-muted">
                        Every booking and purchase helps preserve traditional tribal art, culture, and sustainable tourism practices.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-gradient-primary text-white text-center">
    <div class="container">
        <h2 class="h1 mb-4">Ready to Experience Authentic Tribal Tourism?</h2>
        <p class="lead mb-5">
            Join thousands of travelers who have discovered the beauty and richness of Jharkhand's tribal heritage
        </p>
        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
            <?php if (!isLoggedIn()): ?>
                <a href="<?php echo APP_URL; ?>includes/pages/auth/register.php" class="btn btn-light btn-lg">
                    <i class="fas fa-user-plus me-2"></i>Join Our Community
                </a>
                <a href="<?php echo APP_URL; ?>includes/pages/auth/login.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-sign-in-alt me-2"></i>Login to Explore
                </a>
            <?php else: ?>
                <a href="<?php echo APP_URL; ?>dashboard/index.php" class="btn btn-light btn-lg">
                    <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                </a>
                <a href="<?php echo APP_URL; ?>includes/pages/public/places.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-compass me-2"></i>Explore Places
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
/* Homepage Specific Styles */
.place-card {
    transition: transform var(--transition-normal), box-shadow var(--transition-normal);
}

.place-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-xl);
}

.place-image-container {
    position: relative;
    overflow: hidden;
}

.place-image {
    height: 200px;
    object-fit: cover;
    transition: transform var(--transition-normal);
}

.place-card:hover .place-image {
    transform: scale(1.05);
}

.place-type-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: rgba(255, 255, 255, 0.95);
    color: var(--text-primary);
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius-full);
    font-size: var(--font-size-sm);
    font-weight: 600;
    backdrop-filter: blur(5px);
}

.place-image-placeholder {
    background: var(--bg-light);
    border: 2px dashed #dee2e6;
    height: 200px;
}

.stat-number {
    font-weight: 700;
    color: var(--primary-color);
}

/* Counter Animation */
.counter-animate {
    animation: countUp 2s ease-out;
}

@keyframes countUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .hero h1 {
        font-size: var(--font-size-3xl);
    }

    .place-image {
        height: 150px;
    }

    .stat-item {
        margin-bottom: var(--spacing-lg);
    }
}
</style>

<script>
// Counter animation for statistics
function animateCounters() {
    const counters = document.querySelectorAll('.stat-number');
    const speed = 200; // Animation duration

    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target'));
        const increment = target / speed;

        const updateCounter = () => {
            const current = parseInt(counter.innerText);

            if (current < target) {
                counter.innerText = Math.ceil(current + increment);
                setTimeout(updateCounter, 10);
            } else {
                counter.innerText = target;
                counter.classList.add('counter-animate');
            }
        };

        // Start animation when element is visible
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    updateCounter();
                    observer.unobserve(entry.target);
                }
            });
        });

        observer.observe(counter);
    });
}

// Track homepage interactions
document.addEventListener('DOMContentLoaded', function() {
    animateCounters();

    // Track feature card clicks
    document.querySelectorAll('.feature-card').forEach(card => {
        card.addEventListener('click', function() {
            trackEvent('Homepage', 'Feature Click', this.querySelector('h5').textContent);
        });
    });

    // Track CTA button clicks
    document.querySelectorAll('.hero .btn').forEach(btn => {
        btn.addEventListener('click', function() {
            trackEvent('Homepage', 'CTA Click', this.textContent.trim());
        });
    });

    // Add smooth scroll behavior
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Add parallax effect to hero section
    const hero = document.querySelector('.hero');
    if (hero) {
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const parallax = scrolled * 0.5;
            hero.style.transform = `translateY(${parallax}px)`;
        });
    }
});

// Performance monitoring
if ('PerformanceObserver' in window) {
    const perfObserver = new PerformanceObserver((list) => {
        for (const entry of list.getEntries()) {
            if (entry.entryType === 'measure') {
                console.log(`Performance: ${entry.name} = ${entry.duration}ms`);
            }
        }
    });
    perfObserver.observe({ entryTypes: ['measure'] });
}

// Mark page load
performance.mark('homepage-fully-loaded');
</script>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>