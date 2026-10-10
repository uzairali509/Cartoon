<?php
/**
 * signup.php — Signup page with database registration and avatar upload.
 */

require_once __DIR__ . '/includes/config.php';

$page = 'signup';
$pageTitle = 'Sign Up — Cartoon Universe';
$fullIntro = false;

// Redirect if already logged in
redirectIfLoggedIn('dashboard.php');

$message = '';
$message_type = '';
$errors = [];
$name = '';
$email = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';
        
        // Validate name
        if (strlen($name) < 2) {
            $errors[] = 'Please enter your name (at least 2 characters).';
        } elseif (strlen($name) > 40) {
            $errors[] = 'Name must be less than 40 characters.';
        }
        
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        
        // Validate password
        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }
        
        // Validate password match
        if ($password !== $password2) {
            $errors[] = 'Passwords do not match.';
        }
        
        // Handle avatar upload - REQUIRED
        $avatarPath = null;
        if (!isset($_FILES['picture']) || $_FILES['picture']['error'] === UPLOAD_ERR_NO_FILE) {
            $errors[] = 'Please choose an avatar image. It is required.';
        } else {
            $uploadResult = handleAvatarUpload($_FILES['picture']);
            if (!$uploadResult['success']) {
                $errors[] = $uploadResult['message'];
            } else {
                $avatarPath = $uploadResult['path'];
            }
        }
        
        // If no errors, register user
        if (empty($errors)) {
            $result = registerUser($name, $email, $password, $avatarPath);
            
            if ($result['success']) {
                setUserSession($result['user']);
                setFlash('ok', $result['message']);
                header('Location: dashboard.php');
                exit;
            } else {
                $errors[] = $result['message'];
            }
        }
        
        if (!empty($errors)) {
            $message = implode('<br>', $errors);
            $message_type = 'error';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<main class="page-wrap auth-wrap">
  <section class="page-card auth-card">
    <!-- Avatar Upload Section (Left on desktop, top on mobile) -->
    <div class="auth-avatar-section">
      <div class="auth-avatar-content">
        <h2>Your Avatar</h2>
        <p>Choose a picture to represent you in the Cartoon Universe</p>
        
        <div class="avatar-upload-section">
          <label for="picture" class="avatar-upload-label">
            <div class="avatar-preview" id="avatarPreview">
              <div class="default-avatar" id="defaultAvatar">
                <div class="upload-icon">
                  <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="4"></circle><path d="M12 14c-4.42 0-8 3.58-8 8v2h16v-2c0-4.42-3.58-8-8-8z"></path></svg>
                </div>
                <div class="upload-title">YOUR AVATAR</div>
                <div class="upload-text">Choose a picture</div>
              </div>
              <img id="avatarImage" class="avatar-image" src="" alt="Selected Avatar">
              <div class="avatar-overlay">
                <label for="picture" class="choose-another-btn">CHOOSE ANOTHER PIC</label>
              </div>
            </div>
          </label>
          
          <input type="file" id="picture" name="picture" accept="image/png,image/jpeg,image/webp" hidden required>
          
          <div class="upload-hint">PNG, JPG or WEBP (max 2MB) <span style="color: var(--accent-primary);">*</span></div>
          <div id="imageValidationMessage" class="form-message" style="display: none;"></div>
        </div>
      </div>
    </div>
    
    <!-- Signup Form Section -->
    <div class="auth-form-section">
      <h1 class="page-title">Create Account</h1>
      <p class="page-sub">Fill in your details to get started</p>
      
      <?php if (!empty($message)): ?>
        <div class="form-message <?= $message_type === 'success' ? 'success' : 'error' ?>">
          <span class="message-icon">
            <?= $message_type === 'success' ? '✓' : '!' ?>
          </span>
          <span><?= $message ?></span>
        </div>
      <?php endif; ?>
      
      <form method="post" action="" class="auth-fields" enctype="multipart/form-data">
        <?= csrfField() ?>
        
        <label>
          YOUR NAME
          <input type="text" name="name" required maxlength="40" placeholder="Ember" value="<?= e($name) ?>">
        </label>
        
        <label>
          EMAIL
          <input type="email" name="email" required placeholder="example@example.com" value="<?= e($email) ?>">
        </label>
        
        <label>
          PASSWORD
          <input type="password" name="password" required minlength="6" placeholder="At least 6 characters">
        </label>
        
        <label>
          REPEAT PASSWORD
          <input type="password" name="password2" required placeholder="Once more, for the fox">
        </label>
        
        <button class="btn-primary" type="submit" name="signup">CREATE ACCOUNT</button>
      </form>
      
      <p class="auth-alt">Already a member? <a href="login.php">Log in here</a>.</p>
    </div>
  </section>
</main>

<!-- JavaScript for image preview and client-side validation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('picture');
    const avatarImage = document.getElementById('avatarImage');
    const defaultAvatar = document.getElementById('defaultAvatar');
    const avatarPreview = document.getElementById('avatarPreview');
    const validationMessage = document.getElementById('imageValidationMessage');
    const uploadHint = document.querySelector('.upload-hint');
    
    const MAX_SIZE = 2 * 1024 * 1024; // 2MB
    const ALLOWED_TYPES = ['image/png', 'image/jpeg', 'image/webp'];
    const ALLOWED_EXTS = ['png', 'jpg', 'jpeg', 'webp'];
    
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        validationMessage.style.display = 'none';
        validationMessage.className = 'form-message';
        validationMessage.innerHTML = '';
        
        if (!file) {
            // Reset to default
            avatarImage.src = '';
            avatarPreview.classList.remove('has-image');
            uploadHint.textContent = 'PNG, JPG or WEBP (max 2MB)';
            uploadHint.style.color = '';
            return;
        }
        
        // Client-side validation
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
        
        // Show preview
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
    
    // Form validation on submit
    const form = document.querySelector('.auth-fields');
    form.addEventListener('submit', function(e) {
        console.log('Signup form submit triggered');
        const file = fileInput.files[0];
        let hasError = false;
        
        // Image is required
        if (!file) {
            e.preventDefault();
            showValidationError('Please choose an avatar image. It is required.');
            hasError = true;
        } else {
            const ext = file.name.split('.').pop().toLowerCase();
            if (!ALLOWED_EXTS.includes(ext)) {
                e.preventDefault();
                showValidationError('Invalid file type. Only PNG, JPG, and WEBP are allowed.');
                hasError = true;
            }
            if (file.size > MAX_SIZE) {
                e.preventDefault();
                showValidationError('File size must be less than 2MB.');
                hasError = true;
            }
        }
        
        if (!hasError) {
            console.log('Signup form validation passed, submitting...');
        }
    });
    
    // Store avatar in sessionStorage after successful signup for login page
    form.addEventListener('submit', function() {
        const file = fileInput.files[0];
        const nameInput = document.querySelector('input[name="name"]');
        if (file && nameInput.value) {
            // Create a blob URL for the preview to use on login page
            const reader = new FileReader();
            reader.onload = function(e) {
                sessionStorage.setItem('signup_avatar', e.target.result);
                sessionStorage.setItem('signup_name', nameInput.value);
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>