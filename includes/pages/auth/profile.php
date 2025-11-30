<?php
/**
 * Digital Tribal Heritage (DTH) - User Profile Management
 * Allow users to view and edit their profile information
 */

require_once __DIR__ . '/../../config/config.php';

// Require login
requireLogin();

$errors = [];
$success = '';
$user = null;

// Get current user information
$sql = "SELECT * FROM users WHERE id = ?";
$user = fetchOne($db, $sql, [$_SESSION['user_id']]);

if (!$user) {
    // User not found, logout and redirect
    session_destroy();
    redirect(APP_URL . 'includes/pages/auth/login.php', 'User not found. Please login again.', 'error');
}

// Handle profile update
if (isPost() && verifyCSRFToken(post('csrf_token'))) {
    $name = post('name');
    $email = post('email');
    $phone = post('phone');
    $address = post('address');
    $currentPassword = post('current_password');
    $newPassword = post('new_password');
    $confirmPassword = post('confirm_password');

    // Validate basic fields
    if (empty($name)) {
        $errors[] = 'Name is required.';
    } elseif (!isValidName($name)) {
        $errors[] = 'Name should only contain letters and spaces (2-100 characters).';
    }

    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!isValidEmail($email)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif ($email !== $user['email'] && recordExists($db, 'users', 'email = ? AND id != ?', [$email, $_SESSION['user_id']])) {
        $errors[] = 'Email address already exists. Please use a different email.';
    }

    if (empty($phone)) {
        $errors[] = 'Phone number is required.';
    } elseif (!isValidPhone($phone)) {
        $errors[] = 'Please enter a valid 10-digit Indian mobile number starting with 6-9.';
    }

    // Validate password change if provided
    if (!empty($newPassword)) {
        if (empty($currentPassword)) {
            $errors[] = 'Current password is required to change password.';
        } elseif (!verifyPassword($currentPassword, $user['password'])) {
            $errors[] = 'Current password is incorrect.';
        } elseif (!isValidPassword($newPassword)) {
            $errors[] = 'New password must be at least 8 characters long and contain at least one letter and one number.';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'New passwords do not match.';
        }
    }

    // Handle profile image upload
    $profileImage = $user['profile_image']; // Keep current image
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadResult = uploadFile(
            $_FILES['profile_image'],
            UPLOAD_PATH,
            ['jpg', 'jpeg', 'png', 'gif'],
            1048576 // 1MB
        );

        if ($uploadResult['success']) {
            // Delete old image if it exists
            if (!empty($user['profile_image'])) {
                $oldImagePath = UPLOAD_PATH . $user['profile_image'];
                $oldThumbPath = UPLOAD_PATH . 'thumb_' . $user['profile_image'];

                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
                if (file_exists($oldThumbPath)) {
                    unlink($oldThumbPath);
                }
            }

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

    // If no errors, update profile
    if (empty($errors)) {
        $db->beginTransaction();

        try {
            // Update user information
            $sql = "UPDATE users SET name = ?, email = ?, phone = ?, address = ?, profile_image = ? WHERE id = ?";
            $params = [$name, $email, $phone, $address, $profileImage, $_SESSION['user_id']];

            if (updateRecord($db, $sql, $params)) {
                // Update password if provided
                if (!empty($newPassword)) {
                    $hashedPassword = hashPassword($newPassword);
                    $sql = "UPDATE users SET password = ? WHERE id = ?";
                    updateRecord($db, $sql, [$hashedPassword, $_SESSION['user_id']]);
                }

                $db->commit();

                // Update session data
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_image'] = $profileImage;

                // Log profile update
                logUserAction('Profile update', "User updated profile information");

                // Refresh user data
                $sql = "SELECT * FROM users WHERE id = ?";
                $user = fetchOne($db, $sql, [$_SESSION['user_id']]);

                $success = 'Profile updated successfully!';
            } else {
                $db->rollback();
                $errors[] = 'Failed to update profile. Please try again.';
            }
        } catch (Exception $e) {
            $db->rollback();
            $errors[] = 'Database error occurred. Please try again.';
        }
    }
}

// Handle account deletion request
if (isPost() && post('action') === 'delete_account' && verifyCSRFToken(post('csrf_token'))) {
    $deletePassword = post('delete_password');

    if (empty($deletePassword)) {
        $errors[] = 'Password is required to delete account.';
    } elseif (!verifyPassword($deletePassword, $user['password'])) {
        $errors[] = 'Incorrect password. Account not deleted.';
    } else {
        // Begin deletion process
        $db->beginTransaction();

        try {
            // Soft delete user (set is_active to false)
            $sql = "UPDATE users SET is_active = 0 WHERE id = ?";
            updateRecord($db, $sql, [$_SESSION['user_id']]);

            // Log account deletion
            logUserAction('Account deletion', "User deleted their account");

            $db->commit();

            // Destroy session and redirect
            session_destroy();
            redirect(APP_URL . 'includes/pages/auth/login.php', 'Your account has been deleted successfully.', 'success');
        } catch (Exception $e) {
            $db->rollback();
            $errors[] = 'Failed to delete account. Please try again.';
        }
    }
}

// Get user statistics based on role
$userStats = [];
if ($user['role'] === ROLE_TOURIST) {
    // Tourist statistics
    $stats = [
        'bookings' => "SELECT COUNT(*) as count FROM bookings WHERE user_id = ?",
        'reviews' => "SELECT COUNT(*) as count FROM reviews WHERE user_id = ?",
        'art_orders' => "SELECT COUNT(*) as count FROM art_orders WHERE buyer_user_id = ?"
    ];

    foreach ($stats as $key => $sql) {
        $result = fetchOne($db, $sql, [$_SESSION['user_id']]);
        $userStats[$key] = $result['count'];
    }
} elseif ($user['role'] === ROLE_TRIBAL) {
    // Tribal user statistics
    $stats = [
        'homestays' => "SELECT COUNT(*) as count FROM homestays WHERE tribal_user_id = ?",
        'guides' => "SELECT COUNT(*) as count FROM guides WHERE tribal_user_id = ?",
        'art_items' => "SELECT COUNT(*) as count FROM art_items WHERE tribal_user_id = ?",
        'blogs' => "SELECT COUNT(*) as count FROM blogs WHERE author_id = ?",
        'received_bookings' => "SELECT COUNT(*) as count FROM bookings b
                              JOIN homestays h ON b.homestay_id = h.id
                              WHERE h.tribal_user_id = ?",
        'art_sales' => "SELECT COUNT(*) as count FROM art_orders ao
                          JOIN art_items ai ON ao.art_item_id = ai.id
                          WHERE ai.tribal_user_id = ?"
    ];

    foreach ($stats as $key => $sql) {
        $result = fetchOne($db, $sql, [$_SESSION['user_id']]);
        $userStats[$key] = $result['count'];
    }
}

$pageTitle = 'My Profile - ' . APP_NAME;
include __DIR__ . '/../../includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <!-- Profile Sidebar -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <?php if (!empty($user['profile_image'])): ?>
                            <img src="<?php echo APP_URL . 'assets/images/uploads/thumb_' . htmlspecialchars($user['profile_image']); ?>"
                                 class="rounded-circle img-thumbnail" alt="Profile Picture" style="width: 150px; height: 150px; object-fit: cover;">
                        <?php else: ?>
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                 style="width: 150px; height: 150px; margin: 0 auto;">
                                <i class="fas fa-user fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <h5 class="card-title"><?php echo htmlspecialchars($user['name']); ?></h5>
                    <p class="text-muted mb-2"><?php echo htmlspecialchars($user['email']); ?></p>

                    <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'tribal' ? 'success' : 'primary'); ?> fs-6">
                        <?php echo ucfirst($user['role']); ?>
                    </span>

                    <?php if ($user['role'] === ROLE_TRIBAL): ?>
                        <div class="mt-3">
                            <small class="text-muted">Tribal Community Member</small>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Stats -->
                <div class="card-footer bg-light">
                    <h6 class="text-center mb-3">My Activity</h6>
                    <?php if (!empty($userStats)): ?>
                        <div class="row text-center">
                            <?php foreach ($userStats as $key => $value): ?>
                                <div class="col-6 mb-2">
                                    <div class="fw-bold text-primary"><?php echo $value; ?></div>
                                    <small class="text-muted"><?php echo ucwords(str_replace('_', ' ', $key)); ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">No activity yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="col-lg-8">
            <!-- Profile Form -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Edit Profile</h5>
                </div>
                <div class="card-body">
                    <?php displayMessage(); ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Please fix the following errors:</h6>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" id="profileForm">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label fw-bold">Full Name *</label>
                                <input type="text" class="form-control" id="name" name="name"
                                       value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                <div class="form-text">As per your government ID</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-bold">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                <div class="form-text">We'll never share your email with anyone else</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label fw-bold">Mobile Number *</label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                       value="<?php echo htmlspecialchars($user['phone']); ?>"
                                       pattern="[6-9][0-9]{9}" maxlength="10" required>
                                <div class="form-text">10-digit Indian mobile number</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="profile_image" class="form-label fw-bold">Profile Photo</label>
                                <input type="file" class="form-control" id="profile_image" name="profile_image"
                                       accept="image/*">
                                <div class="form-text">JPG, PNG or GIF. Max 1MB. Square image recommended.</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label fw-bold">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            <div class="form-text">For delivery of purchased art items</div>
                        </div>

                        <hr>

                        <h6 class="mb-3"><i class="fas fa-lock me-2"></i>Change Password</h6>
                        <p class="text-muted mb-3">Leave blank if you don't want to change your password</p>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save Changes
                            </button>
                            <a href="<?php echo APP_URL; ?>dashboard/index.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Account Actions -->
            <div class="card shadow-sm border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Danger Zone</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Warning:</strong> Deleting your account is permanent and cannot be undone. All your data, bookings, and content will be permanently removed.
                    </div>

                    <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                        <i class="fas fa-trash-alt me-1"></i>Delete My Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteAccountModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Delete Account
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="deleteAccountForm">
                <input type="hidden" name="action" value="delete_account">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <h6 class="alert-heading">This action cannot be undone!</h6>
                        <p class="mb-0">Deleting your account will permanently remove:</p>
                        <ul class="mb-0 mt-2">
                            <li>Your profile information</li>
                            <li>All booking history</li>
                            <li>Reviews and ratings</li>
                            <?php if ($user['role'] === ROLE_TRIBAL): ?>
                                <li>Your listed homestays, guides, and art items</li>
                                <li>Blog posts and stories</li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <p>Please enter your password to confirm account deletion:</p>

                    <div class="mb-3">
                        <label for="delete_password" class="form-label fw-bold">Password</label>
                        <input type="password" class="form-control" id="delete_password" name="delete_password" required>
                        <div class="form-text">Enter your current password to confirm</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt me-1"></i>Delete My Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Password validation
document.getElementById('new_password').addEventListener('input', function(e) {
    const password = e.target.value;
    const confirmPassword = document.getElementById('confirm_password');

    if (confirmPassword.value && password !== confirmPassword.value) {
        confirmPassword.classList.add('is-invalid');
        confirmPassword.classList.remove('is-valid');
    } else if (confirmPassword.value) {
        confirmPassword.classList.add('is-valid');
        confirmPassword.classList.remove('is-invalid');
    }
});

document.getElementById('confirm_password').addEventListener('input', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = e.target.value;

    if (newPassword && confirmPassword !== newPassword) {
        e.target.classList.add('is-invalid');
        e.target.classList.remove('is-valid');
    } else if (newPassword) {
        e.target.classList.add('is-valid');
        e.target.classList.remove('is-invalid');
    }
});

// Form validation
document.getElementById('profileForm').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (newPassword && newPassword !== confirmPassword) {
        e.preventDefault();
        alert('New passwords do not match!');
        return false;
    }

    if (newPassword && newPassword.length < 8) {
        e.preventDefault();
        alert('New password must be at least 8 characters long!');
        return false;
    }

    // Show loading state
    const submitButton = e.target.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
    submitButton.disabled = true;

    // Re-enable after 5 seconds (in case of network issues)
    setTimeout(() => {
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    }, 5000);
});

// Delete account form validation
document.getElementById('deleteAccountForm').addEventListener('submit', function(e) {
    const password = document.getElementById('delete_password').value;

    if (!password) {
        e.preventDefault();
        alert('Please enter your password to confirm account deletion.');
        return false;
    }

    if (!confirm('Are you absolutely sure you want to delete your account? This action cannot be undone.')) {
        e.preventDefault();
        return false;
    }

    // Show loading state
    const submitButton = e.target.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Deleting...';
    submitButton.disabled = true;
});

// Profile image preview
document.getElementById('profile_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const maxSize = 1048576; // 1MB

    if (file && file.size > maxSize) {
        alert('Profile image must be less than 1MB.');
        e.target.value = '';
        return;
    }

    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // You could show a preview here if desired
            console.log('Profile image selected:', file.name);
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>