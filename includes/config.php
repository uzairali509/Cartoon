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

/**
 * Check if local video exists for a show/episode
 */
function hasLocalVideo(PDO $pdo, int $tmdbId, string $mediaType, $season = null, $episode = null): bool {
    $sql = "SELECT 1 FROM videos WHERE tmdb_id = ? AND media_type = ? AND status = 'ready'";
    $params = [$tmdbId, $mediaType];
    if ($season) { $sql .= " AND season_number = ?"; $params[] = $season; }
    if ($episode) { $sql .= " AND episode_number = ?"; $params[] = $episode; }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch() !== false;
}

/**
 * Render a show/movie card (available globally)
 */
if (!function_exists('renderShowCard')) {
    function renderShowCard(array $show, bool $showType = true): string {
        $title = htmlspecialchars($show['title'] ?? '');
        $year = !empty($show['first_air_date']) ? substr($show['first_air_date'], 0, 4) : (substr($show['release_date'] ?? '', 0, 4));
        $rating = !empty($show['vote_average']) ? number_format($show['vote_average'], 1) : 'N/A';
        $posterUrl = $show['poster_url'] ?? '';
        $mediaType = $show['media_type'] ?? ($showType ? 'tv' : 'movie');
        $id = $show['tmdb_id'] ?? $show['id'] ?? 0;
        $genres = $show['genres'] ?? [];
        $genreNames = [];
        $genreMap = [
            16 => 'Animation', 10751 => 'Family', 10759 => 'Action & Adventure',
            10762 => 'Kids', 10765 => 'Sci-Fi & Fantasy', 35 => 'Comedy',
            18 => 'Drama', 12 => 'Adventure', 14 => 'Fantasy', 9648 => 'Mystery',
            28 => 'Action', 10762 => 'Kids', 10759 => 'Action & Adventure'
        ];
        foreach (array_slice($show['genre_ids'] ?? [], 0, 2) as $gid) {
            if (isset($genreMap[$gid])) $genreNames[] = $genreMap[$gid];
        }
        $genreStr = implode(', ', $genreNames);
        
        // Check for local video
        $pdo = getConnection();
        $hasVideo = hasLocalVideo($pdo, $id, $mediaType);
        $watchUrl = $hasVideo ? "watch.php?show_id=$id" : ($mediaType === 'tv' ? "show.php?id=$id" : "movie.php?id=$id");
        $watchText = $hasVideo ? 'WATCH NOW' : 'VIEW DETAILS';
        $watchIcon = $hasVideo ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>' : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>';
        
        return '
        <article class="content-card" data-id="' . $id . '">
            <a href="' . ($mediaType === 'tv' ? "show.php?id=$id" : "movie.php?id=$id") . '" class="card-link">
                <div class="card-poster">
                    ' . ($posterUrl ? '<img src="' . htmlspecialchars($posterUrl) . '" alt="" loading="lazy">' : '<div class="poster-placeholder"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="18" rx="2" ry="2"></rect><circle cx="12" cy="12" r="4"></circle></svg></div>') . '
                    <span class="card-type">' . htmlspecialchars($mediaType === 'tv' ? 'TV Show' : 'Movie') . '</span>
                    ' . ($hasVideo ? '<span class="card-video-badge">▶ PLAY</span>' : '') . '
                </div>
                <div class="card-info">
                    <h3 class="card-title">' . $title . '</h3>
                    <div class="card-meta">
                        ' . ($year ? '<span class="year">' . htmlspecialchars($year) . '</span>' : '') . '
                        <span class="rating">★ ' . htmlspecialchars($rating) . '</span>
                    </div>
                    ' . ($genreStr ? '<div class="card-genres">' . htmlspecialchars($genreStr) . '</div>' : '') . '
                </div>
            </a>
            <a href="' . $watchUrl . '" class="card-watch-btn" style="width: 100%; margin-top: 8px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px; background: ' . ($hasVideo ? 'var(--accent-primary)' : 'var(--bg-secondary)') . '; color: ' . ($hasVideo ? '#fff' : 'var(--text-primary)') . '; border: 1px solid ' . ($hasVideo ? 'var(--accent-primary)' : 'var(--border-default)') . '; border-radius: var(--radius-md); font-weight: 600; font-size: .85rem; text-decoration: none; transition: var(--transition-fast);">
                ' . $watchIcon . '
                <span>' . $watchText . '</span>
            </a>
        </article>';
    }
}

if (!function_exists('renderSection')) {
    function renderSection(string $title, array $items, bool $showType = true, string $viewAllUrl = ''): string {
        if (empty($items)) return '';
        $viewAllLink = $viewAllUrl ? '<a href="' . htmlspecialchars($viewAllUrl) . '" class="view-all">View All →</a>' : '';
        $cards = '';
        foreach ($items as $item) {
            $cards .= renderShowCard($item, $showType);
        }
        return '
        <section class="content-section">
            <header class="section-header">
                <h2 class="section-title">' . htmlspecialchars($title) . '</h2>
                ' . $viewAllLink . '
            </header>
            <div class="content-carousel" data-carousel>
                <div class="carousel-track">' . $cards . '</div>
            </div>
        </section>';
    }
}