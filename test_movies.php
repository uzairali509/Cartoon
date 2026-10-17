<?php
require_once __DIR__ . '/config/api.php';
require_once __DIR__ . '/services/TmdbService.php';

$tmdb = new TmdbService();

echo "Testing Popular Movies...\n";
$result = $tmdb->getPopularMovies(1);
if (isset($result['error'])) {
    echo 'Error: ' . $result['error'] . PHP_EOL;
} else {
    echo 'Results count: ' . count($result['results'] ?? []) . PHP_EOL;
    if (!empty($result['results'])) {
        echo 'First item: ' . ($result['results'][0]['title'] ?? 'N/A') . PHP_EOL;
        echo 'Total results: ' . count($result['results']) . PHP_EOL;
    }
}

echo "\nTesting Trending Movies...\n";
$result2 = $tmdb->getTrendingMovies('week', 1);
if (isset($result2['error'])) {
    echo 'Error: ' . $result2['error'] . PHP_EOL;
} else {
    echo 'Results count: ' . count($result2['results'] ?? []) . PHP_EOL;
}