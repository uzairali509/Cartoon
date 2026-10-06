<?php
/**
 * Database schema extensions for favorites, likes, and watch history
 * 
 * Run this once to create the required tables.
 * Or include in database.php initDatabase() function.
 */

function createSocialTables(PDO $pdo): void {
    // Favorites table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS favorites (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            tmdb_id INTEGER NOT NULL,
            media_type TEXT NOT NULL CHECK (media_type IN ('tv', 'movie')),
            title TEXT NOT NULL,
            poster_path TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(user_id, tmdb_id, media_type),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );
        
        CREATE INDEX IF NOT EXISTS idx_favorites_user ON favorites(user_id);
        CREATE INDEX IF NOT EXISTS idx_favorites_media ON favorites(tmdb_id, media_type);
    ");

    // Likes table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS likes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            tmdb_id INTEGER NOT NULL,
            media_type TEXT NOT NULL CHECK (media_type IN ('tv', 'movie')),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(user_id, tmdb_id, media_type),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );
        
        CREATE INDEX IF NOT EXISTS idx_likes_user ON likes(user_id);
        CREATE INDEX IF NOT EXISTS idx_likes_media ON likes(tmdb_id, media_type);
    ");

    // Watch history table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS watch_history (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            tmdb_id INTEGER NOT NULL,
            media_type TEXT NOT NULL CHECK (media_type IN ('tv', 'movie')),
            season_number INTEGER,
            episode_number INTEGER,
            episode_id INTEGER,
            title TEXT NOT NULL,
            poster_path TEXT,
            progress REAL DEFAULT 0, -- 0.0 to 1.0
            completed BOOLEAN DEFAULT 0,
            watched_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(user_id, episode_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );
        
        CREATE INDEX IF NOT EXISTS idx_watch_history_user ON watch_history(user_id);
        CREATE INDEX IF NOT EXISTS idx_watch_history_media ON watch_history(tmdb_id, media_type);
        CREATE INDEX IF NOT EXISTS idx_watch_history_episode ON watch_history(episode_id);
    ");

    // User preferences/settings
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_preferences (
            user_id INTEGER PRIMARY KEY,
            theme TEXT DEFAULT 'dark',
            language TEXT DEFAULT 'en',
            autoplay BOOLEAN DEFAULT 1,
            subtitles BOOLEAN DEFAULT 0,
            subtitle_language TEXT DEFAULT 'en',
            video_quality TEXT DEFAULT 'auto',
            email_notifications BOOLEAN DEFAULT 1,
            push_notifications BOOLEAN DEFAULT 1,
            mature_content BOOLEAN DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );
    ");

    // Search history
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS search_history (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            query TEXT NOT NULL,
            results_count INTEGER DEFAULT 0,
            searched_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );
        
        CREATE INDEX IF NOT EXISTS idx_search_history_user ON search_history(user_id);
    ");

    // Videos table for local video uploads
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS videos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            tmdb_id INTEGER NOT NULL,
            media_type TEXT NOT NULL CHECK (media_type IN ('tv', 'movie')),
            season_number INTEGER,
            episode_number INTEGER,
            title TEXT NOT NULL,
            description TEXT,
            video_path TEXT NOT NULL,
            thumbnail_path TEXT,
            duration INTEGER DEFAULT 0,
            quality TEXT DEFAULT '1080p',
            is_primary BOOLEAN DEFAULT 0,
            uploaded_by INTEGER,
            status TEXT DEFAULT 'processing' CHECK (status IN ('processing', 'ready', 'failed')),
            views INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
        );
        
        CREATE INDEX IF NOT EXISTS idx_videos_media ON videos(tmdb_id, media_type);
        CREATE INDEX IF NOT EXISTS idx_videos_episode ON videos(tmdb_id, media_type, season_number, episode_number);
        CREATE INDEX IF NOT EXISTS idx_videos_status ON videos(status);
    ");
}

// Helper functions for social features

function addFavorite(PDO $pdo, int $userId, int $tmdbId, string $mediaType, string $title, ?string $posterPath = null): array {
    try {
        $stmt = $pdo->prepare("
            INSERT OR REPLACE INTO favorites (user_id, tmdb_id, media_type, title, poster_path, created_at)
            VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
        ");
        $stmt->execute([$userId, $tmdbId, $mediaType, $title, $posterPath]);
        return ['success' => true, 'message' => 'Added to favorites'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to add favorite'];
    }
}

function removeFavorite(PDO $pdo, int $userId, int $tmdbId, string $mediaType): array {
    try {
        $stmt = $pdo->prepare("
            DELETE FROM favorites WHERE user_id = ? AND tmdb_id = ? AND media_type = ?
        ");
        $stmt->execute([$userId, $tmdbId, $mediaType]);
        return ['success' => true, 'message' => 'Removed from favorites'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to remove favorite'];
    }
}

function getFavorites(PDO $pdo, int $userId, string $mediaType = null): array {
    $sql = "SELECT * FROM favorites WHERE user_id = ?";
    $params = [$userId];
    
    if ($mediaType) {
        $sql .= " AND media_type = ?";
        $params[] = $mediaType;
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function isFavorite(PDO $pdo, int $userId, int $tmdbId, string $mediaType): bool {
    $stmt = $pdo->prepare("
        SELECT 1 FROM favorites WHERE user_id = ? AND tmdb_id = ? AND media_type = ?
    ");
    $stmt->execute([$userId, $tmdbId, $mediaType]);
    return $stmt->fetch() !== false;
}

function toggleLike(PDO $pdo, int $userId, int $tmdbId, string $mediaType): array {
    try {
        // Check if already liked
        $stmt = $pdo->prepare("SELECT 1 FROM likes WHERE user_id = ? AND tmdb_id = ? AND media_type = ?");
        $stmt->execute([$userId, $tmdbId, $mediaType]);
        $exists = $stmt->fetch();

        if ($exists) {
            $stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND tmdb_id = ? AND media_type = ?");
            $stmt->execute([$userId, $tmdbId, $mediaType]);
            return ['success' => true, 'liked' => false, 'message' => 'Removed like'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO likes (user_id, tmdb_id, media_type, created_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)");
            $stmt->execute([$userId, $tmdbId, $mediaType]);
            return ['success' => true, 'liked' => true, 'message' => 'Added like'];
        }
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to toggle like'];
    }
}

function getLikeStatus(PDO $pdo, int $userId, int $tmdbId, string $mediaType): bool {
    $stmt = $pdo->prepare("SELECT 1 FROM likes WHERE user_id = ? AND tmdb_id = ? AND media_type = ?");
    $stmt->execute([$userId, $tmdbId, $mediaType]);
    return $stmt->fetch() !== false;
}

function getLikeCount(PDO $pdo, int $tmdbId, string $mediaType): int {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE tmdb_id = ? AND media_type = ?");
    $stmt->execute([$tmdbId, $mediaType]);
    return (int)$stmt->fetchColumn();
}

function getUserLikes(PDO $pdo, int $userId, string $mediaType = null): array {
    $sql = "SELECT * FROM likes WHERE user_id = ?";
    $params = [$userId];
    
    if ($mediaType) {
        $sql .= " AND media_type = ?";
        $params[] = $mediaType;
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addToWatchHistory(PDO $pdo, int $userId, array $data): array {
    try {
        $stmt = $pdo->prepare("
            INSERT OR REPLACE INTO watch_history 
            (user_id, tmdb_id, media_type, season_number, episode_number, episode_id, title, poster_path, progress, completed, watched_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ");
        $stmt->execute([
            $userId,
            $data['tmdb_id'] ?? 0,
            $data['media_type'] ?? 'tv',
            $data['season_number'] ?? null,
            $data['episode_number'] ?? null,
            $data['episode_id'] ?? null,
            $data['title'] ?? '',
            $data['poster_path'] ?? null,
            $data['progress'] ?? 0,
            $data['completed'] ?? 0,
        ]);
        return ['success' => true, 'message' => 'Watch history updated'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to update watch history'];
    }
}

function getWatchHistory(PDO $pdo, int $userId, int $limit = 50): array {
    $stmt = $pdo->prepare("
        SELECT * FROM watch_history WHERE user_id = ? ORDER BY watched_at DESC LIMIT ?
    ");
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getWatchProgress(PDO $pdo, int $userId, int $tmdbId, int $seasonNumber, int $episodeNumber): ?array {
    $stmt = $pdo->prepare("
        SELECT * FROM watch_history 
        WHERE user_id = ? AND tmdb_id = ? AND season_number = ? AND episode_number = ?
    ");
    $stmt->execute([$userId, $tmdbId, $seasonNumber, $episodeNumber]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function addSearchHistory(PDO $pdo, int $userId, string $query, int $resultsCount = 0): void {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO search_history (user_id, query, results_count, searched_at)
            VALUES (?, ?, ?, CURRENT_TIMESTAMP)
        ");
        $stmt->execute([$userId, $query, $resultsCount]);
    } catch (PDOException $e) {
        // Silently fail - search history is not critical
    }
}

function getSearchHistory(PDO $pdo, int $userId, int $limit = 10): array {
    $stmt = $pdo->prepare("
        SELECT * FROM search_history WHERE user_id = ? ORDER BY searched_at DESC LIMIT ?
    ");
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function clearSearchHistory(PDO $pdo, int $userId): void {
    $stmt = $pdo->prepare("DELETE FROM search_history WHERE user_id = ?");
    $stmt->execute([$userId]);
}

function getUserPreferences(PDO $pdo, int $userId): array {
    $stmt = $pdo->prepare("SELECT * FROM user_preferences WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?? [
        'theme' => 'dark',
        'language' => 'en',
        'autoplay' => true,
        'subtitles' => false,
        'subtitle_language' => 'en',
        'video_quality' => 'auto',
        'email_notifications' => true,
        'push_notifications' => true,
        'mature_content' => false,
    ];
}

function updateUserPreferences(PDO $pdo, int $userId, array $preferences): array {
    try {
        $fields = [];
        $values = [$userId];
        
        $allowed = ['theme', 'language', 'autoplay', 'subtitles', 'subtitle_language', 'video_quality', 'email_notifications', 'push_notifications', 'mature_content'];
        
        foreach ($allowed as $field) {
            if (isset($preferences[$field])) {
                $fields[] = "$field = ?";
                $values[] = $preferences[$field];
            }
        }
        
        if (empty($fields)) {
            return ['success' => true, 'message' => 'No changes'];
        }
        
        $fields[] = 'updated_at = CURRENT_TIMESTAMP';
        $sql = "INSERT INTO user_preferences (user_id, " . implode(', ', $allowed) . ", created_at, updated_at)
                VALUES (" . implode(', ', array_merge(['?'], array_fill(0, count($allowed), '?'))) . ", CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                ON CONFLICT(user_id) DO UPDATE SET " . implode(', ', $fields);
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        
        return ['success' => true, 'message' => 'Preferences updated'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to update preferences'];
    }
}