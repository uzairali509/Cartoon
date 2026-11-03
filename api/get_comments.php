<?php
/**
 * get_comments.php — Get comments for video
 */
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

$videoId = (int)($_GET['video_id'] ?? 0);

if (!$videoId) {
    echo json_encode(['success' => false, 'comments' => []]);
    exit;
}

$pdo = getConnection();

// Ensure table exists
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

$stmt = $pdo->prepare("
    SELECT vc.*, u.name as user_name, u.avatar_path as user_avatar
    FROM video_comments vc
    JOIN users u ON vc.user_id = u.id
    WHERE vc.video_id = ?
    ORDER BY vc.created_at DESC
    LIMIT 50
");
$stmt->execute([$videoId]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'comments' => $comments]);