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
// 'mysql' → data is visible in MySQL Workbench / phpMyAdmin (XAMPP default: root, no password)
define('DB_TYPE', 'mysql');

// SQLite configuration
define('SQLITE_PATH', __DIR__ . '/data/cartoon_universe.sqlite');

// MySQL configuration (for production)
define('DB_HOST', 'localhost');
define('DB_NAME', 'cartoon');
define('DB_USER', 'root');
define('DB_PASS', 'uzairmaster509');
define('DB_CHARSET', 'utf8mb4');

/**
 * SQLite connection singleton
 */
$sqliteConnection = null;

/**
 * Get database connection
 */
function getConnection(): PDO {
    global $sqliteConnection;
    
    if (DB_TYPE === 'sqlite') {
        // Return existing connection if available
        if ($sqliteConnection instanceof PDO) {
            return $sqliteConnection;
        }
        
        $dir = dirname(SQLITE_PATH);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $pdo = new PDO('sqlite:' . SQLITE_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // Disable WAL mode to prevent locking issues - must be first command
        $pdo->exec('PRAGMA journal_mode = DELETE');
        // Set busy timeout to 5 seconds
        $pdo->exec('PRAGMA busy_timeout = 5000');
        
        // Store singleton
        $sqliteConnection = $pdo;
        
        return $pdo;
    } else {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        // Connect WITHOUT dbname first so we can auto-create the database
        // (otherwise first run dies with "Unknown database")
        $pdo = new PDO("mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET, DB_USER, DB_PASS, $options);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `" . DB_NAME . "`");
        return $pdo;
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