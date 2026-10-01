<?php
/**
 * api/favorites.php — Favorites API endpoint
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
        case 'add':
            $tmdbId = (int)($input['tmdb_id'] ?? 0);
            $mediaType = $input['media_type'] ?? 'tv';
            $title = $input['title'] ?? '';
            $posterPath = $input['poster_path'] ?? null;
            
            if (!$tmdbId || !$title) {
                throw new Exception('Missing required fields');
            }
            
            $result = addFavorite($pdo, $currentUser['id'], $tmdbId, $mediaType, $title, $posterPath);
            echo json_encode($result);
            break;
            
        case 'remove':
            $favId = (int)($input['fav_id'] ?? 0);
            $mediaType = $input['media_type'] ?? 'tv';
            
            if (!$favId) {
                throw new Exception('Invalid favorite ID');
            }
            
            // Verify ownership
            $stmt = $pdo->prepare("SELECT user_id FROM favorites WHERE id = ?");
            $stmt->execute([$favId]);
            $fav = $stmt->fetch();
            
            if (!$fav || $fav['user_id'] !== $currentUser['id']) {
                throw new Exception('Unauthorized');
            }
            
            $result = removeFavorite($pdo, $currentUser['id'], 0, $mediaType);
            // Actually remove by ID
            $stmt = $pdo->prepare("DELETE FROM favorites WHERE id = ? AND user_id = ?");
            $stmt->execute([$favId, $currentUser['id']]);
            
            echo json_encode(['success' => true, 'message' => 'Removed from favorites']);
            break;
            
        case 'check':
            $tmdbId = (int)($input['tmdb_id'] ?? 0);
            $mediaType = $input['media_type'] ?? 'tv';
            
            $isFav = isFavorite($pdo, $currentUser['id'], $tmdbId, $mediaType);
            echo json_encode(['success' => true, 'is_favorite' => $isFav]);
            break;
            
        case 'list':
            $mediaType = $input['media_type'] ?? null;
            $favorites = getFavorites($pdo, $currentUser['id'], $mediaType);
            echo json_encode(['success' => true, 'favorites' => $favorites]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}