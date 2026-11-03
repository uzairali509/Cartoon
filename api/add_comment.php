<?php
/**
 * add_comment.php — Add comment to video
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

$videoId = (int)($_POST['video_id'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if (!$videoId || empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Create comments table if not exists
$pdo = getConnection();
if (DB_TYPE === 'mysql') {
    $pdo->exec("CREATE TABLE IF NOT EXISTS video_comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        video_id INT NOT NULL,
        user_id INT NOT NULL,
        comment TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_video_comments_video (video_id),
        INDEX idx_video_comments_user (user_id),
        FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} else {
    $pdo->exec("CREATE TABLE IF NOT EXISTS video_comments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        video_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        comment TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_video_comments_video ON video_comments(video_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_video_comments_user ON video_comments(user_id)");
}

$stmt = $pdo->prepare("INSERT INTO video_comments (video_id, user_id, comment) VALUES (?, ?, ?)");
$stmt->execute([$videoId, $currentUser['id'], $comment]);

echo json_encode(['success' => true, 'comment_id' => $pdo->lastInsertId()]);