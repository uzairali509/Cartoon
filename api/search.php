<?php
/**
 * api/search.php — Search suggestions API endpoint
 */

require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');
$limit = min(max((int)($_GET['limit'] ?? 5), 1), 10);

if (strlen($query) < 2) {
    echo json_encode(['success' => true, 'results' => []]);
    exit;
}

// Check if TMDB is configured
if (!$tmdb->isConfigured()) {
    http_response_code(503);
    echo json_encode([
        'success' => false, 
        'message' => 'TMDB API not configured. Please add TMDB_API_KEY to your .env file.',
        'results' => []
    ]);
    exit;
}

try {
    $results = $tmdb->searchMulti($query, 1);
    
    // Handle API errors
    if (isset($results['error'])) {
        http_response_code(503);
        echo json_encode([
            'success' => false, 
            'message' => $results['error'],
            'results' => []
        ]);
        exit;
    }
    
    $suggestions = [];
    
    if ($results && !empty($results['results'])) {
        foreach ($results['results'] as $result) {
            $mediaType = $result['media_type'] ?? '';
            
            // Skip people for suggestions
            if ($mediaType === 'person') continue;
            
            // Filter for animation/family content
            $genreIds = $result['genre_ids'] ?? [];
            $isAnimation = in_array(16, $genreIds);
            $isFamily = in_array(10751, $genreIds);
            $isKids = in_array(10762, $genreIds);
            
            $title = $result['name'] ?? $result['title'] ?? '';
            $overview = $result['overview'] ?? '';
            $hasAnimationKeywords = stripos($title, 'cartoon') !== false || 
                                   stripos($title, 'animated') !== false ||
                                   stripos($overview, 'cartoon') !== false ||
                                   stripos($overview, 'animated') !== false;
            
            if ($isAnimation || $isFamily || $isKids || $hasAnimationKeywords) {
                $suggestions[] = [
                    'id' => $result['id'],
                    'tmdb_id' => $result['id'],
                    'title' => $title,
                    'media_type' => $mediaType,
                    'poster_path' => $result['poster_path'] ?? '',
                    'poster_url' => $tmdb->getImageUrl($result['poster_path'] ?? ''),
                    'first_air_date' => $result['first_air_date'] ?? '',
                    'release_date' => $result['release_date'] ?? '',
                    'vote_average' => $result['vote_average'] ?? 0,
                    'genre_ids' => $result['genre_ids'] ?? [],
                    'origin_country' => $result['origin_country'] ?? [],
                ];
            }
            
            if (count($suggestions) >= $limit) break;
        }
    }
    
    echo json_encode(['success' => true, 'results' => $suggestions]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Search failed', 'results' => []]);
}