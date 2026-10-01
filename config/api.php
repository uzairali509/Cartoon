<?php
/**
 * api.php — API Configuration
 * 
 * Store API keys and configuration for external services.
 * Create a .env file in the project root with your API keys.
 * Example .env file:
 * TMDB_API_KEY=your_actual_api_key_here
 */

// Load .env file if it exists
$envPath = __DIR__ . '/../../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

// TMDB (The Movie Database) API
// Get your API key from: https://www.themoviedb.org/settings/api
define('TMDB_API_KEY', getenv('TMDB_API_KEY') ?: '');

// Check if API key is configured
if (empty(TMDB_API_KEY)) {
    error_log('WARNING: TMDB_API_KEY is not set. Please add TMDB_API_KEY to your .env file or environment variables.');
}

define('TMDB_BASE_URL', 'https://api.themoviedb.org/3');
define('TMDB_IMAGE_BASE_URL', 'https://image.tmdb.org/t/p');

// Image sizes: w92, w154, w185, w342, w500, w780, original
define('TMDB_POSTER_SIZE', 'w342');
define('TMDB_BACKDROP_SIZE', 'w780');
define('TMDB_PROFILE_SIZE', 'w185');

// TMDB Genre IDs for Animation
// 16 = Animation, 10751 = Family, 10759 = Action & Adventure, 10762 = Kids, 10765 = Sci-Fi & Fantasy
define('TMDB_ANIMATION_GENRE_ID', 16);
define('TMDB_FAMILY_GENRE_ID', 10751);
define('TMDB_KIDS_GENRE_ID', 10762);

// Cache configuration
define('API_CACHE_TTL', 3600); // 1 hour
define('API_CACHE_DIR', __DIR__ . '/../storage/cache/api');

// Rate limiting
define('TMDB_RATE_LIMIT', 40); // requests per 10 seconds

// Video Player Configuration
// Note: This platform uses TMDB for metadata only. 
// Video playback requires licensed streaming sources.
// Configure your video provider here when available.
define('VIDEO_PROVIDER_ENABLED', false);
define('VIDEO_PROVIDER_BASE_URL', '');