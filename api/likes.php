<?php
/**
 * api/likes.php — Likes API endpoint
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
        case 'toggle':
            $tmdbId = (int)($input['tmdb_id'] ?? 0);
            $mediaType = $input['media_type'] ?? 'tv';
            
            if (!$tmdbId) {
                throw new Exception('Missing TMDB ID');
            }
            
            $result = toggleLike($pdo, $currentUser['id'], $tmdbId, $mediaType);
            // Add count
            $result['count'] = getLikeCount($pdo, $tmdbId, $mediaType);
            echo json_encode($result);
            break;
            
        case 'status':
            $tmdbId = (int)($input['tmdb_id'] ?? 0);
            $mediaType = $input['media_type'] ?? 'tv';
            
            $liked = getLikeStatus($pdo, $currentUser['id'], $tmdbId, $mediaType);
            $count = getLikeCount($pdo, $tmdbId, $mediaType);
            echo json_encode(['success' => true, 'liked' => $liked, 'count' => $count]);
            break;
            
        case 'count':
            $tmdbId = (int)($input['tmdb_id'] ?? 0);
            $mediaType = $input['media_type'] ?? 'tv';
            
            $count = getLikeCount($pdo, $tmdbId, $mediaType);
            echo json_encode(['success' => true, 'count' => $count]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}