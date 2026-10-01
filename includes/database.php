<?php
/**
 * database.php — Database configuration and connection.
 * 
 * Uses SQLite for development. Replace with MySQL for production.
 * 
 * To switch to MySQL:
 * 1. Change DB_TYPE to 'mysql'
 * 2. Set DB_HOST, DB_NAME, DB_USER, DB_PASS
 * 3. Update getConnection() to use PDO MySQL
 */

// Database type: 'sqlite' or 'mysql'
define('DB_TYPE', 'sqlite');

// SQLite configuration
define('SQLITE_PATH', __DIR__ . '/data/cartoon_universe.sqlite');

// MySQL configuration (for production)
define('DB_HOST', 'localhost');
define('DB_NAME', 'cartoon_universe');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get database connection
 */
function getConnection(): PDO {
    if (DB_TYPE === 'sqlite') {
        $dir = dirname(SQLITE_PATH);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $pdo = new PDO('sqlite:' . SQLITE_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } else {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    }
}

/**
 * Initialize database tables
 */
function initDatabase(): void {
    $pdo = getConnection();
    
    if (DB_TYPE === 'sqlite') {
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
    } else {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                avatar_path VARCHAR(500) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            
            CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
        ");
    }
}

// Initialize on include
initDatabase();

// Create social tables
createSocialTables(getConnection());