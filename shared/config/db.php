<?php

/**
 * Database configuration and PDO connection
 */
class Database
{
    private static $instance = null;
    private $connection;

    private $host = 'localhost';
    private $database = 'trabalho_web';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    private $socket = '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock';

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct()
    {
        $dsn = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset};unix_socket={$this->socket}";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new PDOException("Database connection failed");
        }
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get database connection
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}
