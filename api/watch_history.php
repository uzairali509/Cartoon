<?php
/**
 * api/watch_history.php — Watch history API endpoint
 */

require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

if (!$currentUser) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

$pdo = getConnection();

try {
    switch ($action) {
        case 'update':
            $data = [
                'tmdb_id' => (int)($input['tmdb_id'] ?? 0),
                'media_type' => $input['media_type'] ?? 'tv',
                'season_number' => (int)($input['season_number'] ?? 0),
                'episode_number' => (int)($input['episode_number'] ?? 0),
                'episode_id' => (int)($input['episode_id'] ?? 0),
                'title' => $input['title'] ?? '',
                'poster_path' => $input['poster_path'] ?? null,
                'progress' => (float)($input['progress'] ?? 0),
                'completed' => (bool)($input['completed'] ?? false),
            ];
            
            if (!$data['tmdb_id'] || !$data['title']) {
                throw new Exception('Missing required fields');
            }
            
            $result = addToWatchHistory($pdo, $currentUser['id'], $data);
            echo json_encode($result);
            break;
            
        case 'get':
            $limit = min(max((int)($_GET['limit'] ?? 20), 1), 100);
            $history = getWatchHistory($pdo, $currentUser['id'], $limit);
            echo json_encode(['success' => true, 'history' => $history]);
            break;
            
        case 'progress':
            $tmdbId = (int)($_GET['tmdb_id'] ?? 0);
            $mediaType = $_GET['media_type'] ?? 'tv';
            $seasonNumber = (int)($_GET['season'] ?? 0);
            $episodeNumber = (int)($_GET['episode'] ?? 0);
            
            $progress = getWatchProgress($pdo, $currentUser['id'], $tmdbId, $seasonNumber, $episodeNumber);
            echo json_encode(['success' => true, 'progress' => $progress]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}