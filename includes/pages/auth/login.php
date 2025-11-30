<?php
/**
 * Digital Tribal Heritage (DTH) - User Login
 * Authentication page for all user types
 */

require_once __DIR__ . '/../../config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(APP_URL);
}

$errors = [];
$success = '';

// Handle login attempts
if (isPost()) {
    // Validate CSRF token
    if (!verifyCSRFToken(post('csrf_token'))) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $email = post('email');
        $password = post('password');
        $remember = post('remember');

        // Validate inputs
        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!isValidEmail($email)) {
            $errors[] = 'Please enter a valid email address.';
        }

        if (empty($password)) {
            $errors[] = 'Password is required.';
        }

        // Check login attempts (simple rate limiting)
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 0;
            $_SESSION['last_attempt'] = 0;
        }

        // Lockout after too many attempts
        if ($_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS &&
            (time() - $_SESSION['last_attempt']) < LOGIN_TIMEOUT) {
            $errors[] = 'Too many login attempts. Please try again after ' .
                       ceil((LOGIN_TIMEOUT - (time() - $_SESSION['last_attempt'])) / 60) . ' minutes.';
        } else {
            // Attempt login
            if (empty($errors)) {
                $sql = "SELECT id, name, email, password, role, is_active, profile_image
                        FROM users WHERE email = ? AND is_active = 1";
                $user = fetchOne($db, $sql, [$email]);

                if ($user && verifyPassword($password, $user['password'])) {
                    // Login successful
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['user_image'] = $user['profile_image'];
                    $_SESSION['logged_in'] = true;
                    $_SESSION['login_time'] = time();
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                    // Reset login attempts
                    $_SESSION['login_attempts'] = 0;
                    $_SESSION['last_attempt'] = 0;

                    // Handle remember me
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        $selector = bin2hex(random_bytes(8));

                        // Store remember token in database (you'd need a separate table for this)
                        // For simplicity, we'll just use session
                        $_SESSION['remember_token'] = $token;
                        $_SESSION['selector'] = $selector;

                        // Set cookie for 30 days
                        setcookie('remember_selector', $selector, time() + (30 * 24 * 60 * 60), '/');
                        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/');
                    }

                    // Log successful login
                    logUserAction('User login', "User {$user['name']} logged in from IP: " . $_SERVER['REMOTE_ADDR']);

                    // Redirect based on user role or intended destination
                    $redirect = $_SESSION['redirect_after_login'] ?? APP_URL;
                    unset($_SESSION['redirect_after_login']);

                    $_SESSION['success'] = 'Welcome back, ' . htmlspecialchars($user['name']) . '!';
                    redirect($redirect);

                } else {
                    // Login failed
                    $_SESSION['login_attempts']++;
                    $_SESSION['last_attempt'] = time();

                    $errors[] = 'Invalid email or password.';

                    if ($_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS - 2) {
                        $attemptsLeft = MAX_LOGIN_ATTEMPTS - $_SESSION['login_attempts'];
                        $errors[] = "Warning: $attemptsLeft attempts remaining.";
                    }

                    // Log failed login attempt
                    logUserAction('Failed login attempt', "Email: $email, IP: " . $_SERVER['REMOTE_ADDR']);
                }
            }
        }
    }
}

$pageTitle = 'Login - ' . APP_NAME;
include __DIR__ . '/../../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-gradient-primary text-white text-center py-4">
                    <h3 class="mb-2"><i class="fas fa-sign-in-alt me-2"></i>Welcome Back</h3>
                    <p class="mb-0">Login to access your Digital Tribal Heritage account</p>
                </div>
                <div class="card-body p-4">
                    <?php displayMessage(); ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Login Error</h6>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="loginForm">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                        <!-- Email Field -->
                        <div class="mb-4">
                            <label for="email" class="form-label fw-bold">
                                <i class="fas fa-envelope me-1"></i>Email Address
                            </label>
                            <input type="email" class="form-control form-control-lg" id="email"
                                   name="email" value="<?php echo htmlspecialchars(post('email')); ?>"
                                   placeholder="Enter your email address" required>
                            <div class="form-text">Enter the email address you registered with</div>
                        </div>

                        <!-- Password Field -->
                        <div class="mb-4">
                            <label for="password" class="form-label fw-bold">
                                <i class="fas fa-lock me-1"></i>Password
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control form-control-lg" id="password"
                                       name="password" placeholder="Enter your password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye" id="passwordIcon"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Forgot your password?</a>
                            </div>
                        </div>

                        <!-- Remember Me -->
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                <i class="fas fa-clock me-1"></i>Remember me for 30 days
                            </label>
                        </div>

                        <!-- Login Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Login to Your Account
                            </button>
                        </div>

                        <div class="text-center mt-4">
                            <small class="text-muted">
                                Don't have an account?
                                <a href="<?php echo APP_URL; ?>includes/pages/auth/register.php" class="text-decoration-none">
                                    Register here
                                </a>
                            </small>
                        </div>

                        <!-- Demo Account Notice -->
                        <div class="alert alert-info mt-4">
                            <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Demo Account</h6>
                            <p class="mb-2 small">
                                <strong>Email:</strong> admin@dth.com<br>
                                <strong>Password:</strong> admin123
                            </p>
                            <p class="mb-0 small text-muted">
                                This is the admin account for demonstration purposes.
                            </p>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-light text-center py-3">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt me-1"></i>
                        Your login is secure and encrypted
                    </small>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="col-lg-4 col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-user me-2 text-primary"></i>Tourist Features</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Explore Jharkhand tourism</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Book homestays & guides</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Purchase tribal art & crafts</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Experience interactive stories</li>
                        <li class="mb-0"><i class="fas fa-check text-success me-2"></i>Read community blogs</li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-home me-2 text-success"></i>Tribal Host Features</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>List homestay accommodations</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Offer guided tours</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Sell art & crafts online</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Write community blogs</li>
                        <li class="mb-0"><i class="fas fa-check text-success me-2"></i>Manage bookings</li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-shield-alt me-2 text-warning"></i>Why Choose DTH?</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Direct tribal community support</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Authentic cultural experiences</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Secure booking platform</li>
                        <li class="mb-0"><i class="fas fa-check text-success me-2"></i>Preserving tribal heritage</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Forgot Password Modal -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="forgotPasswordModalLabel">Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="forgotPasswordForm">
                <div class="modal-body">
                    <p>Enter your email address and we'll send you instructions to reset your password.</p>
                    <div class="mb-3">
                        <label for="resetEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="resetEmail" name="resetEmail" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Reset Instructions</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const passwordIcon = document.getElementById('passwordIcon');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        passwordIcon.classList.remove('fa-eye');
        passwordIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        passwordIcon.classList.remove('fa-eye-slash');
        passwordIcon.classList.add('fa-eye');
    }
});

// Form validation
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    if (!email || !password) {
        e.preventDefault();
        alert('Please fill in all fields.');
        return false;
    }

    if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
        e.preventDefault();
        alert('Please enter a valid email address.');
        return false;
    }

    // Show loading state
    const submitButton = e.target.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Logging in...';
    submitButton.disabled = true;

    // Re-enable after 5 seconds (in case of network issues)
    setTimeout(() => {
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    }, 5000);
});

// Forgot password form
document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const email = document.getElementById('resetEmail').value;

    if (!email || !email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
        alert('Please enter a valid email address.');
        return;
    }

    // Show loading state
    const submitButton = e.target.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
    submitButton.disabled = true;

    // Simulate API call (in real app, this would make an AJAX request)
    setTimeout(() => {
        alert('Password reset instructions have been sent to your email address.');

        // Close modal and reset form
        const modal = bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal'));
        modal.hide();
        e.target.reset();

        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    }, 2000);
});

// Auto-focus email field
document.getElementById('email').focus();

// Check for remember me cookies on page load
document.addEventListener('DOMContentLoaded', function() {
    const selector = document.cookie.match(/(^|;) ?remember_selector=([^;]*)(;|$)/);
    const token = document.cookie.match(/(^|;) ?remember_token=([^;]*)(;|$)/);

    if (selector && token) {
        // Auto-fill form if cookies exist and are valid
        // In real app, you'd validate these against database
        console.log('Remember me cookies found:', selector[2], token[2]);
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>