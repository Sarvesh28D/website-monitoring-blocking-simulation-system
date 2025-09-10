<?php
/**
 * Database Connection Manager for Website Monitoring System
 * 
 * This file provides secure database connectivity using PDO with proper
 * error handling, connection pooling simulation, and security best practices.
 * 
 * @author Expert Software Engineer
 * @version 1.0
 * @created September 10, 2025
 */

class DatabaseConnection {
    private static $instance = null;
    private $pdo;
    private $config;
    
    // Database configuration
    private const DEFAULT_CONFIG = [
        'host' => 'localhost',
        'dbname' => 'website_monitoring',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true // Connection pooling
        ]
    ];
    
    /**
     * Private constructor to prevent direct instantiation
     * Implements Singleton pattern for connection management
     */
    private function __construct() {
        $this->loadConfiguration();
        $this->connect();
    }
    
    /**
     * Load database configuration from environment or config file
     */
    private function loadConfiguration(): void {
        // Try to load from environment variables first (production best practice)
        $this->config = [
            'host' => $_ENV['DB_HOST'] ?? self::DEFAULT_CONFIG['host'],
            'dbname' => $_ENV['DB_NAME'] ?? self::DEFAULT_CONFIG['dbname'],
            'username' => $_ENV['DB_USER'] ?? self::DEFAULT_CONFIG['username'],
            'password' => $_ENV['DB_PASS'] ?? self::DEFAULT_CONFIG['password'],
            'charset' => $_ENV['DB_CHARSET'] ?? self::DEFAULT_CONFIG['charset'],
            'options' => self::DEFAULT_CONFIG['options']
        ];
        
        // Alternative: Load from config file if it exists
        $configFile = __DIR__ . '/config.php';
        if (file_exists($configFile)) {
            $fileConfig = include $configFile;
            if (is_array($fileConfig)) {
                $this->config = array_merge($this->config, $fileConfig);
            }
        }
    }
    
    /**
     * Establish database connection with retry logic
     */
    private function connect(): void {
        $dsn = sprintf(
            "mysql:host=%s;dbname=%s;charset=%s",
            $this->config['host'],
            $this->config['dbname'],
            $this->config['charset']
        );
        
        $maxRetries = 3;
        $retryDelay = 1; // seconds
        
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $this->pdo = new PDO(
                    $dsn,
                    $this->config['username'],
                    $this->config['password'],
                    $this->config['options']
                );
                
                // Test connection
                $this->pdo->query("SELECT 1");
                
                // Set charset
                $this->pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                error_log("Database connection established successfully");
                return;
                
            } catch (PDOException $e) {
                error_log("Database connection attempt {$attempt} failed: " . $e->getMessage());
                
                if ($attempt === $maxRetries) {
                    throw new PDOException("Failed to connect to database after {$maxRetries} attempts: " . $e->getMessage());
                }
                
                sleep($retryDelay);
                $retryDelay *= 2; // Exponential backoff
            }
        }
    }
    
    /**
     * Get singleton instance of database connection
     * 
     * @return DatabaseConnection
     */
    public static function getInstance(): DatabaseConnection {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get PDO connection object
     * 
     * @return PDO
     */
    public function getConnection(): PDO {
        // Check if connection is still alive
        try {
            $this->pdo->query("SELECT 1");
        } catch (PDOException $e) {
            error_log("Database connection lost, reconnecting...");
            $this->connect();
        }
        
        return $this->pdo;
    }
    
    /**
     * Execute a prepared statement with parameters
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind
     * @return PDOStatement
     * @throws PDOException
     */
    public function executeQuery(string $sql, array $params = []): PDOStatement {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query execution failed: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Params: " . json_encode($params));
            throw $e;
        }
    }
    
    /**
     * Fetch all rows from a query
     * 
     * @param string $sql SQL query
     * @param array $params Parameters to bind
     * @return array
     */
    public function fetchAll(string $sql, array $params = []): array {
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Fetch single row from a query
     * 
     * @param string $sql SQL query
     * @param array $params Parameters to bind
     * @return array|false
     */
    public function fetchOne(string $sql, array $params = []) {
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Get the last inserted ID
     * 
     * @return string
     */
    public function getLastInsertId(): string {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction(): void {
        $this->pdo->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit(): void {
        $this->pdo->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback(): void {
        $this->pdo->rollBack();
    }
    
    /**
     * Check if we're in a transaction
     * 
     * @return bool
     */
    public function inTransaction(): bool {
        return $this->pdo->inTransaction();
    }
    
    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization of the instance
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Convenience function to get database connection
 * 
 * @return PDO
 */
function getDatabase(): PDO {
    return DatabaseConnection::getInstance()->getConnection();
}

/**
 * Convenience function to execute query and fetch all results
 * 
 * @param string $sql
 * @param array $params
 * @return array
 */
function queryAll(string $sql, array $params = []): array {
    return DatabaseConnection::getInstance()->fetchAll($sql, $params);
}

/**
 * Convenience function to execute query and fetch single result
 * 
 * @param string $sql
 * @param array $params
 * @return array|false
 */
function queryOne(string $sql, array $params = []) {
    return DatabaseConnection::getInstance()->fetchOne($sql, $params);
}

/**
 * Convenience function to execute query without fetching results
 * 
 * @param string $sql
 * @param array $params
 * @return PDOStatement
 */
function executeQuery(string $sql, array $params = []): PDOStatement {
    return DatabaseConnection::getInstance()->executeQuery($sql, $params);
}

/**
 * Sanitize and validate input data
 * 
 * @param mixed $data
 * @param string $type Expected data type
 * @return mixed
 */
function sanitizeInput($data, string $type = 'string') {
    if ($data === null) {
        return null;
    }
    
    switch ($type) {
        case 'int':
            return filter_var($data, FILTER_VALIDATE_INT);
        case 'float':
            return filter_var($data, FILTER_VALIDATE_FLOAT);
        case 'email':
            return filter_var($data, FILTER_VALIDATE_EMAIL);
        case 'url':
            return filter_var($data, FILTER_VALIDATE_URL);
        case 'string':
        default:
            return is_string($data) ? trim(htmlspecialchars($data, ENT_QUOTES, 'UTF-8')) : $data;
    }
}

/**
 * Format date for display
 * 
 * @param string $date
 * @param string $format
 * @return string
 */
function formatDate(string $date, string $format = 'Y-m-d H:i:s'): string {
    try {
        return (new DateTime($date))->format($format);
    } catch (Exception $e) {
        return $date;
    }
}

/**
 * Calculate percentage safely
 * 
 * @param int|float $part
 * @param int|float $total
 * @param int $decimals
 * @return float
 */
function calculatePercentage($part, $total, int $decimals = 2): float {
    if ($total == 0) {
        return 0.0;
    }
    return round(($part / $total) * 100, $decimals);
}

// Set error reporting for development
if (!defined('PRODUCTION')) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Set timezone
date_default_timezone_set('UTC');

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
