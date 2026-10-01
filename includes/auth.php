<?php
/**
 * auth.php — Authentication functions.
 * 
 * Handles user registration, login, logout, and session management.
 */

require_once __DIR__ . '/database.php';

/**
 * Start session if not already started
 */
function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Hash password
 */
function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 */
function verifyPassword(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

/**
 * Register a new user
 * 
 * @return array ['success' => bool, 'message' => string, 'user' => array|null]
 */
function registerUser(string $name, string $email, string $password, string $avatarPath = null): array {
    $pdo = getConnection();
    
    // Check if email exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'This email is already registered.', 'user' => null];
    }
    
    // Insert user
    $hash = hashPassword($password);
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, avatar_path) VALUES (?, ?, ?, ?)');
    $stmt->execute([$name, $email, $hash, $avatarPath]);
    
    $userId = $pdo->lastInsertId();
    $user = [
        'id' => $userId,
        'name' => $name,
        'email' => $email,
        'avatar_path' => $avatarPath,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    return ['success' => true, 'message' => 'Welcome to the club!', 'user' => $user];
}

/**
 * Login user
 * 
 * @return array ['success' => bool, 'message' => string, 'user' => array|null]
 */
function loginUser(string $email, string $password): array {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user || !verifyPassword($password, $user['password_hash'])) {
        return ['success' => false, 'message' => 'Invalid email or password.', 'user' => null];
    }
    
    // Remove password hash from user data
    unset($user['password_hash']);
    
    return ['success' => true, 'message' => 'Welcome back!', 'user' => $user];
}

/**
 * Set user session
 */
function setUserSession(array $user): void {
    startSession();
    $_SESSION['user'] = $user;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_avatar'] = $user['avatar_path'] ?? null;
    session_regenerate_id(true);
}

/**
 * Get current user from session
 */
function getCurrentUser(): ?array {
    startSession();
    return $_SESSION['user'] ?? null;
}

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool {
    return getCurrentUser() !== null;
}

/**
 * Logout user
 */
function logoutUser(): void {
    startSession();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
    // Don't start a new session - let the calling page handle it
}

/**
 * Require login (redirect if not logged in)
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

/**
 * Redirect if already logged in
 */
function redirectIfLoggedIn(string $redirectTo = 'dashboard.php'): void {
    if (isLoggedIn()) {
        header('Location: ' . $redirectTo);
        exit;
    }
}

/**
 * Get user by ID
 */
function getUserById(int $id): ?array {
    $pdo = getConnection();
    $stmt = $pdo->prepare('SELECT id, name, email, avatar_path, created_at FROM users WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

/**
 * Update user avatar
 */
function updateUserAvatar(int $userId, string $avatarPath): bool {
    $pdo = getConnection();
    $stmt = $pdo->prepare('UPDATE users SET avatar_path = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
    return $stmt->execute([$avatarPath, $userId]);
}

/**
 * Generate CSRF token
 */
function generateCsrfToken(): string {
    startSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCsrfToken(string $token): bool {
    startSession();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Set flash message
 */
function setFlash(string $type, string $message): void {
    startSession();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear flash message
 */
function getFlash(): ?array {
    startSession();
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

/**
 * Handle avatar upload
 * 
 * @return array ['success' => bool, 'path' => string|null, 'message' => string]
 */
function handleAvatarUpload(array $file): array {
    $allowedTypes = ['image/png', 'image/jpeg', 'image/webp'];
    $allowedExts = ['png', 'jpg', 'jpeg', 'webp'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'path' => null, 'message' => 'Upload failed. Error code: ' . $file['error']];
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'path' => null, 'message' => 'File size must be less than 2MB.'];
    }
    
    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'path' => null, 'message' => 'Invalid image format. Only PNG, JPG, and WEBP are allowed.'];
    }
    
    // Check extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExts)) {
        return ['success' => false, 'path' => null, 'message' => 'Invalid file extension.'];
    }
    
    // Generate unique filename
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $uploadDir = __DIR__ . '/../assets/uploads/avatars/';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $destination = $uploadDir . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'path' => null, 'message' => 'Failed to save uploaded file.'];
    }
    
    return ['success' => true, 'path' => 'assets/uploads/avatars/' . $filename, 'message' => 'Avatar uploaded successfully.'];
}