<?php
/**
 * profile.php — User profile editing page
 */

require_once __DIR__ . '/includes/config.php';

$page = 'profile';
$pageTitle = 'Profile — Cartoon Universe';
$fullIntro = false;

// Require login
requireLogin();

$currentUser = getCurrentUser();
$message = '';
$message_type = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid request. Please try again.';
        $message_type = 'error';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        $errors = [];
        
        if (strlen($name) < 2) {
            $errors[] = 'Name must be at least 2 characters.';
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address.';
        }
        
        // Check if email changed and if it's already taken
        if ($email !== $currentUser['email']) {
            $pdo = getConnection();
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
            $stmt->execute([$email, $currentUser['id']]);
            if ($stmt->fetch()) {
                $errors[] = 'This email is already in use.';
            }
        }
        
        // Handle password change
        if ($currentPassword || $newPassword || $confirmPassword) {
            if (!$currentPassword) {
                $errors[] = 'Current password is required to change password.';
            } elseif (!verifyPassword($currentPassword, getConnection()->prepare('SELECT password_hash FROM users WHERE id = ?')->execute([$currentUser['id']])->fetchColumn())) {
                $errors[] = 'Current password is incorrect.';
            }
            
            if ($newPassword !== $confirmPassword) {
                $errors[] = 'New passwords do not match.';
            }
            
            if (strlen($newPassword) > 0 && strlen($newPassword) < 6) {
                $errors[] = 'New password must be at least 6 characters.';
            }
        }
        
        // Handle avatar upload
        $avatarPath = $currentUser['avatar_path'];
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = handleAvatarUpload($_FILES['avatar']);
            if (!$uploadResult['success']) {
                $errors[] = $uploadResult['message'];
            } else {
                $avatarPath = $uploadResult['path'];
            }
        }
        
        if (empty($errors)) {
            $pdo = getConnection();
            
            if ($newPassword) {
                $hash = hashPassword($newPassword);
                $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, password_hash = ?, avatar_path = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
                $stmt->execute([$name, $email, $hash, $avatarPath, $currentUser['id']]);
            } else {
                $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, avatar_path = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
                $stmt->execute([$name, $email, $avatarPath, $currentUser['id']]);
            }
            
            // Update session
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_avatar'] = $avatarPath;
            $currentUser = getUserById($currentUser['id']);
            
            $message = 'Profile updated successfully!';
            $message_type = 'success';
        } else {
            $message = implode('<br>', $errors);
            $message_type = 'error';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<main class="page-wrap profile-page">
  <section class="page-card">
    <header class="page-head">
      <h1 class="page-title">Profile Settings</h1>
      <p class="page-sub">Manage your account settings and preferences</p>
    </header>

    <div class="profile-content">
      <!-- Sidebar -->
      <div class="profile-sidebar">
        <div class="profile-menu">
          <a href="#" class="active">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            <span>Account</span>
          </a>
          <a href="favorites.php">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
            <span>Favorites</span>
          </a>
          <a href="dashboard.php">
            <svg width="20" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="18" rx="2"></rect><polyline points="16 21 12 15 8 21"></polyline></svg>
            <span>Dashboard</span>
          </a>
          <a href="logout.php">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            <span>Log Out</span>
          </a>
        </div>
      </div>

      <!-- Main Content -->
      <div class="profile-tabs">
        <div class="tab-panel">
          <?php if ($message): ?>
            <div class="form-message <?= $message_type === 'success' ? 'success' : 'error' ?>">
              <span class="message-icon"><?= $message_type === 'success' ? '✓' : '!' ?></span>
              <span><?= $message ?></span>
            </div>
          <?php endif; ?>
          
          <form method="post" action="" class="auth-fields" enctype="multipart/form-data" style="max-width: 600px;">
            <?= csrfField() ?>
            
            <!-- Avatar Section -->
            <div class="avatar-upload-section">
              <label for="avatar" class="avatar-upload-label">
                <div class="avatar-preview" id="avatarPreview">
                  <div class="default-avatar" id="defaultAvatar">
                    <div class="upload-icon">
                      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="4"></circle><path d="M12 14c-4.42 0-8 3.58-8 8v2h16v-2c0-4.42-3.58-8-8-8z"></path></svg>
                    </div>
                    <div class="upload-title">YOUR AVATAR</div>
                    <div class="upload-text">Click to change</div>
                  </div>
                  <img id="avatarImage" class="avatar-image" src="" alt="Selected Avatar">
                  <div class="avatar-overlay">
                    <label for="avatar" class="choose-another-btn">CHANGE AVATAR</label>
                  </div>
                </div>
              </label>
              
              <input type="file" id="avatar" name="avatar" accept="image/png,image/jpeg,image/webp" hidden>
              
              <div class="upload-hint">PNG, JPG or WEBP (max 2MB)</div>
              <div id="imageValidationMessage" class="form-message" style="display: none;"></div>
            </div>
            
            <hr style="border: none; border-top: 1px solid var(--border-subtle); margin: 24px 0;">
            
            <!-- Name -->
            <label>
              YOUR NAME
              <input type="text" name="name" required maxlength="40" value="<?= e($currentUser['name']) ?>">
            </label>
            
            <!-- Email -->
            <label>
              EMAIL
              <input type="email" name="email" required value="<?= e($currentUser['email']) ?>">
            </label>
            
            <hr style="border: none; border-top: 1px solid var(--border-subtle); margin: 24px 0;">
            
            <!-- Password Change Section -->
            <h3 style="font-family: var(--font-display); font-weight: 600; color: var(--accent-primary); margin-bottom: 16px; font-size: 1.1rem;">Change Password</h3>
            <p style="font-size: .8rem; color: var(--text-muted); margin-bottom: 16px;">Leave blank to keep current password</p>
            
            <label>
              CURRENT PASSWORD
              <input type="password" name="current_password" placeholder="Enter current password">
            </label>
            
            <label>
              NEW PASSWORD
              <input type="password" name="new_password" minlength="6" placeholder="At least 6 characters">
            </label>
            
            <label>
              CONFIRM NEW PASSWORD
              <input type="password" name="confirm_password" placeholder="Repeat new password">
            </label>
            
            <button class="btn-primary" type="submit" name="update_profile" style="width: 100%; margin-top: 8px;">SAVE CHANGES</button>
          </form>
        </div>
      </div>
    </div>
  </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('avatar');
    const avatarImage = document.getElementById('avatarImage');
    const defaultAvatar = document.getElementById('defaultAvatar');
    const avatarPreview = document.getElementById('avatarPreview');
    const validationMessage = document.getElementById('imageValidationMessage');
    const uploadHint = document.querySelector('.upload-hint');
    
    const MAX_SIZE = 2 * 1024 * 1024;
    const ALLOWED_TYPES = ['image/png', 'image/jpeg', 'image/webp'];
    const ALLOWED_EXTS = ['png', 'jpg', 'jpeg', 'webp'];
    
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        validationMessage.style.display = 'none';
        validationMessage.className = 'form-message';
        validationMessage.innerHTML = '';
        
        if (!file) {
            avatarImage.src = '';
            avatarPreview.classList.remove('has-image');
            uploadHint.textContent = 'PNG, JPG or WEBP (max 2MB)';
            uploadHint.style.color = '';
            return;
        }
        
        const ext = file.name.split('.').pop().toLowerCase();
        
        if (!ALLOWED_EXTS.includes(ext)) {
            showValidationError('Invalid file type. Only PNG, JPG, and WEBP are allowed.');
            fileInput.value = '';
            return;
        }
        
        if (file.size > MAX_SIZE) {
            showValidationError('File size must be less than 2MB. Current: ' + formatFileSize(file.size));
            fileInput.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            avatarImage.src = e.target.result;
            avatarPreview.classList.add('has-image');
        };
        reader.readAsDataURL(file);
        
        uploadHint.textContent = file.name + ' (' + formatFileSize(file.size) + ')';
        uploadHint.style.color = 'var(--accent-secondary)';
    });
    
    function showValidationError(msg) {
        validationMessage.style.display = 'flex';
        validationMessage.classList.add('error');
        validationMessage.innerHTML = '<span class="message-icon">!</span><span>' + msg + '</span>';
    }
    
    function formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>