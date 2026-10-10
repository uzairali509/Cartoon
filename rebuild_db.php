<?php
// Rebuild the database with the correct schema
$newDbPath = 'C:\xampp\htdocs\cartoon-universe\includes\data\cartoon_universe.sqlite';

try {
    // Remove the corrupted database
    if (file_exists($newDbPath)) {
        unlink($newDbPath);
    }
    if (file_exists($newDbPath . '-wal')) {
        unlink($newDbPath . '-wal');
    }
    if (file_exists($newDbPath . '-shm')) {
        unlink($newDbPath . '-shm');
    }

    // Create new database with the correct schema
    $pdo = new PDO('sqlite:' . $newDbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create users table (from database.php initDatabase)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            avatar_path VARCHAR(500) DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
    ");

    // Create social tables (from SocialService.php)
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
            progress REAL DEFAULT 0,
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
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
        );
        
        CREATE INDEX IF NOT EXISTS idx_videos_media ON videos(tmdb_id, media_type);
        CREATE INDEX IF NOT EXISTS idx_videos_episode ON videos(tmdb_id, media_type, season_number, episode_number);
        CREATE INDEX IF NOT EXISTS idx_videos_status ON videos(status);
    ");

    echo "Database rebuilt successfully!\n";
    
    // Verify
    $pdo->query('PRAGMA integrity_check');
    echo "Integrity check passed.\n";
    
    // Show tables
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
    while ($row = $tables->fetch()) {
        echo "Table: " . $row[0] . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}