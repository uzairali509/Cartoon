<?php
/**
 * config.php — Configuration and constants for Cartoon Universe.
 * 
 * Initializes session, database, and provides helper functions.
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Site configuration
define('SITE_NAME', 'Cartoon Universe');
define('SITE_URL', 'http://localhost/cartoon-universe');

// Page configuration
$page = $page ?? 'home';
$pageTitle = $pageTitle ?? 'Cartoon Universe';
$fullIntro = $fullIntro ?? false;

// Include services first (needed by database)
require_once __DIR__ . '/../services/TmdbService.php';
require_once __DIR__ . '/../services/SocialService.php';

// Include database and auth
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';

// Initialize database
initDatabase();

// TMDB Service instance (available globally)
$tmdb = new TmdbService();

// Navigation items
$navItems = [
    ['href' => 'index.php', 'label' => 'Home', 'page' => 'home'],
    ['href' => 'shows.php', 'label' => 'Shows', 'page' => 'shows'],
    ['href' => 'categories.php', 'label' => 'Movies', 'page' => 'movies'],
    ['href' => 'characters.php', 'label' => 'Characters', 'page' => 'characters'],
    ['href' => 'episodes.php', 'label' => 'Episodes', 'page' => 'episodes'],
];

// Current user
$currentUser = getCurrentUser();

// Flash message
$flash = getFlash();

// Auth links - dynamic based on login state
if ($currentUser) {
    $authLinks = [
        'logout' => 'logout.php',
        'dashboard' => 'dashboard.php',
        'profile' => 'profile.php',
        'favorites' => 'favorites.php',
    ];
} else {
    $authLinks = [
        'login' => 'login.php',
        'signup' => 'signup.php',
    ];
}

/**
 * Navigation active state helper
 */
function isActive($page, $target) {
    return $page === $target ? ' active' : '';
}

/**
 * Escape HTML
 */
function e($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF field
 */
function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(generateCsrfToken()) . '">';
}