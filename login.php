<?php
/**
 * login.php — Login page with database authentication.
 */

require_once __DIR__ . '/includes/config.php';

$page = 'login';
$pageTitle = 'Log In — Cartoon Universe';
$fullIntro = false;

// Redirect if already logged in
redirectIfLoggedIn('dashboard.php');

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid request. Please try again.');
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        $errors = [];
        
        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        
        if (empty($password)) {
            $errors[] = 'Password is required.';
        }
        
        if (empty($errors)) {
            $result = loginUser($email, $password);
            
            if ($result['success']) {
                setUserSession($result['user']);
                setFlash('ok', $result['message']);
                
                // Handle remember me
                if ($remember) {
                    // Extend session lifetime
                    session_set_cookie_params(30 * 24 * 60 * 60); // 30 days
                }
                
                // Check for redirect
                $redirect = $_GET['redirect'] ?? 'dashboard.php';
                header('Location: ' . $redirect);
                exit;
            } else {
                setFlash('error', $result['message']);
            }
        } else {
            setFlash('error', implode('<br>', $errors));
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<main class="page-wrap auth-wrap">
  <section class="page-card auth-card">
    <!-- Left side - Brand/Art -->
    <div class="auth-art">
      <div class="auth-art-content">
        <div class="auth-badge">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M12 2.5l2.6 5.9 6.4.6-4.9 4.2 1.5 6.3L12 16.3 6.4 19.5l1.5-6.3L3 9l6.4-.6z" fill="#FFFDF7" />
          </svg>
        </div>
        <h2>Welcome Back</h2>
        <p>Sign in to continue your journey through the Cartoon Universe</p>
      </div>
    </div>
    
    <!-- Right side - Login Form -->
    <div class="auth-form-container">
      <h1 class="page-title">Log In</h1>
      <p class="page-sub">Enter your credentials to access your account</p>
      
      <?php if ($flash): ?>
        <div class="form-message <?= $flash['type'] === 'ok' ? 'success' : 'error' ?>">
          <span class="message-icon">
            <?= $flash['type'] === 'ok' ? '✓' : '!' ?>
          </span>
          <span><?= e($flash['message']) ?></span>
        </div>
      <?php endif; ?>
      
      <form method="post" action="" class="auth-fields">
        <?= csrfField() ?>
        <label>
          EMAIL
          <input type="email" name="email" required placeholder="you@example.com" autocomplete="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </label>
        <label>
          PASSWORD
          <input type="password" name="password" required placeholder="••••••" autocomplete="current-password">
        </label>
        <label class="remember-me">
          <input type="checkbox" name="remember" id="remember" <?= isset($_POST['remember']) ? 'checked' : '' ?>>
          <span>Remember me</span>
        </label>
        <button class="btn-primary" type="submit" name="login">LOG IN</button>
      </form>
      
      <p class="auth-alt">New around here? <a href="signup.php">Create an account</a></p>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>