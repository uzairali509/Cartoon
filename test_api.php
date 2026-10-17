<?php
require_once __DIR__ . '/config/api.php';
require_once __DIR__ . '/services/TmdbService.php';

$tmdb = new TmdbService();
echo 'API Key configured: ' . ($tmdb->isConfigured() ? 'Yes' : 'No') . PHP_EOL;

// Test trending TV
$result = $tmdb->getTrendingTv('week', 1);
if (isset($result['error'])) {
    echo 'Error: ' . $result['error'] . PHP_EOL;
} else {
    echo 'Trending TV Results count: ' . count($result['results'] ?? []) . PHP_EOL;
}

// Test popular movies
$result = $tmdb->getPopularMovies(1);
if (isset($result['error'])) {
    echo 'Movies Error: ' . $result['error'] . PHP_EOL;
} else {
    echo 'Popular Movies count: ' . count($result['results'] ?? []) . PHP_EOL;
    if (!empty($result['results'])) {
        echo 'First movie keys: ' . implode(', ', array_keys($result['results'][0])) . PHP_EOL;
    }
}

// Test formatting
if (!empty($result['results'])) {
    $formatted = $tmdb->formatMovie($result['results'][0]);
    echo 'Formatted movie keys: ' . implode(', ', array_keys($formatted)) . PHP_EOL;
    echo 'tmdb_id: ' . $formatted['tmdb_id'] . PHP_EOL;
    echo 'title: ' . $formatted['title'] . PHP_EOL;
    echo 'poster_url: ' . $formatted['poster_url'] . PHP_EOL;
}