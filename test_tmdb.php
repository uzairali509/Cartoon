<?php
require_once __DIR__ . '/config/api.php';
require_once __DIR__ . '/services/TmdbService.php';

$tmdb = new TmdbService();
echo 'API Key configured: ' . ($tmdb->isConfigured() ? 'YES' : 'NO') . PHP_EOL;

$result = $tmdb->getTrendingTv('week', 1);
if (isset($result['error'])) {
    echo 'Error: ' . $result['error'] . PHP_EOL;
} else {
    echo 'Results count: ' . count($result['results'] ?? []) . PHP_EOL;
    if (!empty($result['results'])) {
        echo 'First item: ' . ($result['results'][0]['name'] ?? 'N/A') . PHP_EOL;
        echo 'Total results: ' . count($result['results']) . PHP_EOL;
    }
}