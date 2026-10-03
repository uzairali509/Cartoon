<?php
/**
 * save_progress.php — Save video watch progress
 */
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$videoId = (int)($input['video_id'] ?? 0);
$progress = (float)($input['progress'] ?? 0);
$currentTime = (float)($input['current_time'] ?? 0);

if (!$videoId) {
    echo json_encode(['success' => false, 'message' => 'Invalid video ID']);
    exit;
}

// Get video info to get tmdb_id, season, episode
$pdo = getConnection();
$stmt = $pdo->prepare("SELECT tmdb_id, media_type, season_number, episode_number, duration FROM videos WHERE id = ?");
$stmt->execute([$videoId]);
$video = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$video) {
    echo json_encode(['success' => false, 'message' => 'Video not found']);
    exit;
}

$completed = $progress >= 0.95 ? 1 : 0;

$stmt = $pdo->prepare("
    INSERT OR REPLACE INTO watch_history 
    (user_id, tmdb_id, media_type, season_number, episode_number, episode_id, title, poster_path, progress, completed, watched_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
");

$stmt->execute([
    $currentUser['id'],
    $video['tmdb_id'],
    $video['media_type'],
    $video['season_number'],
    $video['episode_number'],
    $videoId,
    $video['title'],
    $video['thumbnail_path'],
    $progress,
    $completed
]);

echo json_encode(['success' => true]);