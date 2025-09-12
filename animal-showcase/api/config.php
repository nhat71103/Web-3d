<?php
// api/config.php - Database và API configuration
error_reporting(0);
ini_set('display_errors', 0);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'animal_showcase');
define('DB_USER', 'root');
define('DB_PASS', '');

// Meshy AI API configuration
define('MESHY_API_KEY', 'msy_XnLTWmqUoQDm4y3SFiwMyGl5GfzEAsWNU4K8');
define('MESHY_API_BASE', 'https://api.meshy.ai');

// Database class
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 10
            ]);
            $this->connection->query("SELECT 1");
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        if (!$this->connection) {
            throw new Exception("Database connection not available");
        }
        return $this->connection;
    }
    
    private function __clone() {}
    public function __wakeup() {}
}
?>