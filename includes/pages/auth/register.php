<?php
/**
 * Digital Tribal Heritage (DTH) - User Registration
 * Registration form for tourists and tribal users
 */

require_once __DIR__ . '/../../config/config.php';

$errors = [];
$success = '';

if (isPost()) {
    // Validate CSRF token
    if (!verifyCSRFToken(post('csrf_token'))) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        // Get and validate form data
        $name = post('name');
        $email = post('email');
        $password = post('password');
        $confirmPassword = post('confirm_password');
        $phone = post('phone');
        $role = post('role');
        $address = post('address');

        // Validation rules
        if (empty($name)) {
            $errors[] = 'Name is required.';
        } elseif (!isValidName($name)) {
            $errors[] = 'Name should only contain letters and spaces (2-100 characters).';
        }

        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!isValidEmail($email)) {
            $errors[] = 'Please enter a valid email address.';
        } elseif (recordExists($db, 'users', 'email = ?', [$email])) {
            $errors[] = 'Email address already exists. Please use a different email.';
        }

        if (empty($password)) {
            $errors[] = 'Password is required.';
        } elseif (!isValidPassword($password)) {
            $errors[] = 'Password must be at least 8 characters long and contain at least one letter and one number.';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }

        if (empty($phone)) {
            $errors[] = 'Phone number is required.';
        } elseif (!isValidPhone($phone)) {
            $errors[] = 'Please enter a valid 10-digit Indian mobile number starting with 6-9.';
        }

        if (empty($role)) {
            $errors[] = 'Please select your role.';
        } elseif (!in_array($role, [ROLE_TOURIST, ROLE_TRIBAL])) {
            $errors[] = 'Invalid role selected.';
        }

        // Handle profile image upload
        $profileImage = '';
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = uploadFile(
                $_FILES['profile_image'],
                UPLOAD_PATH,
                ['jpg', 'jpeg', 'png', 'gif'],
                1048576 // 1MB
            );

            if ($uploadResult['success']) {
                $profileImage = $uploadResult['filename'];

                // Create thumbnail
                $thumbnailPath = UPLOAD_PATH . 'thumb_' . $uploadResult['filename'];
                createThumbnail(
                    UPLOAD_PATH . $uploadResult['filename'],
                    $thumbnailPath,
                    150,
                    150
                );
            } else {
                $errors = array_merge($errors, $uploadResult['errors']);
            }
        }

        // If no errors, proceed with registration
        if (empty($errors)) {
            // Hash password
            $hashedPassword = hashPassword($password);

            // Insert user data
            $sql = "INSERT INTO users (name, email, password, phone, role, address, profile_image)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $params = [$name, $email, $hashedPassword, $phone, $role, $address, $profileImage];

            $userId = insertRecord($db, $sql, $params);

            if ($userId) {
                // Log registration
                logUserAction("User registration", "New user registered: $name ($email)");

                // Set success message and redirect to login
                $_SESSION['success'] = 'Registration successful! Please login to continue.';
                redirect(APP_URL . 'includes/pages/auth/login.php');
            } else {
                $errors[] = 'Registration failed. Please try again.';
            }
        }
    }
}

$pageTitle = 'Register - ' . APP_NAME;
include __DIR__ . '/../../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">Create Your Account</h4>
                    <p class="mb-0">Join Digital Tribal Heritage to explore tribal culture</p>
                </div>
                <div class="card-body p-4">
                    <?php displayMessage(); ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <h6>Please fix the following errors:</h6>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" id="registerForm">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                        <!-- Role Selection -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">I want to join as:</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="role" id="role_tourist"
                                           value="<?php echo ROLE_TOURIST; ?>" required>
                                    <label class="form-check-label" for="role_tourist">
                                        <i class="fas fa-user me-1"></i>Tourist
                                        <small class="d-block text-muted">Explore places, book homestays & guides</small>
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="role" id="role_tribal"
                                           value="<?php echo ROLE_TRIBAL; ?>" required>
                                    <label class="form-check-label" for="role_tribal">
                                        <i class="fas fa-home me-1"></i>Tribal Host
                                        <small class="d-block text-muted">List homestays, guide services, art & crafts</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Profile Photo (Optional)</label>
                                <input type="file" class="form-control" name="profile_image"
                                       accept="image/*" id="profileImageInput">
                                <div class="form-text">JPG, PNG or GIF. Max 1MB. Square image recommended.</div>
                                <div class="mt-2" id="profileImagePreview"></div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Personal Information -->
                        <h5 class="mb-3"><i class="fas fa-user me-2"></i>Personal Information</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="name" name="name"
                                       value="<?php echo htmlspecialchars(post('name')); ?>" required>
                                <div class="form-text">As per your government ID</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?php echo htmlspecialchars(post('email')); ?>" required>
                                <div class="form-text">We'll never share your email with anyone else</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Mobile Number *</label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                       value="<?php echo htmlspecialchars(post('phone')); ?>"
                                       pattern="[6-9][0-9]{9}" maxlength="10" required>
                                <div class="form-text">10-digit Indian mobile number</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="address" class="form-label">Address (Optional)</label>
                                <textarea class="form-control" id="address" name="address" rows="1"><?php echo htmlspecialchars(post('address')); ?></textarea>
                                <div class="form-text">For delivery of purchased art items</div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Security -->
                        <h5 class="mb-3"><i class="fas fa-lock me-2"></i>Security</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="form-text">Min 8 characters, at least 1 letter and 1 number</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>

                        <!-- Password Strength Indicator -->
                        <div class="mb-3">
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar" id="passwordStrength" role="progressbar"
                                     style="width: 0%"></div>
                            </div>
                            <small class="form-text" id="passwordStrengthText"></small>
                        </div>

                        <hr class="my-4">

                        <!-- Terms and Submit -->
                        <div class="form-check mb-4">
                            <input type="checkbox" class="form-check-input" id="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a>
                                and <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Policy</a>
                            </label>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="<?php echo APP_URL; ?>" class="btn btn-outline-secondary me-md-2">
                                <i class="fas fa-arrow-left me-1"></i>Back to Home
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-user-plus me-1"></i>Create Account
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <small class="text-muted">
                        Already have an account?
                        <a href="<?php echo APP_URL; ?>includes/pages/auth/login.php">Sign in here</a>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>1. Account Responsibilities</h6>
                <p>You are responsible for maintaining the confidentiality of your account credentials.</p>

                <h6>2. Content Guidelines</h6>
                <p>All content must be appropriate, respectful, and comply with local laws and regulations.</p>

                <h6>3. Service Usage</h6>
                <p>Services are provided for legitimate tourism and cultural exchange purposes.</p>

                <h6>4. Payment and Bookings</h6>
                <p>All bookings and purchases are subject to availability and mutual agreement.</p>

                <h6>5. Privacy</h6>
                <p>Your personal information will be protected as outlined in our Privacy Policy.</p>

                <h6>6. Dispute Resolution</h6>
                <p>Disputes will be resolved through the platform's mediation process.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Privacy Modal -->
<div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="privacyModalLabel">Privacy Policy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>Information We Collect</h6>
                <p>We collect personal information including name, email, phone number, and address for account management and service delivery.</p>

                <h6>How We Use Your Information</h6>
                <ul>
                    <li>To provide and maintain our services</li>
                    <li>To process bookings and orders</li>
                    <li>To communicate with you about your account</li>
                    <li>To improve our services</li>
                </ul>

                <h6>Data Security</h6>
                <p>We implement appropriate security measures to protect your personal information.</p>

                <h6>Information Sharing</h6>
                <p>We do not sell or rent your personal information to third parties.</p>

                <h6>Your Rights</h6>
                <p>You have the right to access, update, or delete your personal information.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Password strength checker
document.getElementById('password').addEventListener('input', function(e) {
    const password = e.target.value;
    const strengthBar = document.getElementById('passwordStrength');
    const strengthText = document.getElementById('passwordStrengthText');

    let strength = 0;
    let feedback = '';

    if (password.length >= 8) strength++;
    if (password.match(/[a-z]+/)) strength++;
    if (password.match(/[A-Z]+/)) strength++;
    if (password.match(/[0-9]+/)) strength++;
    if (password.match(/[$@#&!]+/)) strength++;

    strengthBar.style.width = (strength * 20) + '%';

    switch (strength) {
        case 0:
        case 1:
            strengthBar.className = 'progress-bar bg-danger';
            strengthText.textContent = 'Very Weak';
            break;
        case 2:
            strengthBar.className = 'progress-bar bg-warning';
            strengthText.textContent = 'Weak';
            break;
        case 3:
            strengthBar.className = 'progress-bar bg-info';
            strengthText.textContent = 'Good';
            break;
        case 4:
            strengthBar.className = 'progress-bar bg-success';
            strengthText.textContent = 'Strong';
            break;
        case 5:
            strengthBar.className = 'progress-bar bg-success';
            strengthText.textContent = 'Very Strong';
            break;
    }
});

// Profile image preview
document.getElementById('profileImageInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('profileImagePreview');

    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <img src="${e.target.result}" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
            `;
        };
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '';
    }
});

// Form validation
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
        return false;
    }

    if (!document.getElementById('terms').checked) {
        e.preventDefault();
        alert('Please accept the terms and conditions!');
        return false;
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>