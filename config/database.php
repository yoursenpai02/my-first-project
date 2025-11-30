<?php
/**
 * Digital Tribal Heritage (DTH) - Database Configuration
 * Handles MySQL database connection for XAMPP environment
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'dth_database';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';

    public $conn;

    /**
     * Create database connection
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }

    /**
     * Execute prepared statement
     */
    public function executeQuery($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $exception) {
            error_log("Database error: " . $exception->getMessage());
            return false;
        }
    }

    /**
     * Get single record
     */
    public function getSingle($sql, $params = []) {
        $stmt = $this->executeQuery($sql, $params);
        return $stmt ? $stmt->fetch() : false;
    }

    /**
     * Get multiple records
     */
    public function getMultiple($sql, $params = []) {
        $stmt = $this->executeQuery($sql, $params);
        return $stmt ? $stmt->fetchAll() : [];
    }

    /**
     * Insert record and return ID
     */
    public function insert($sql, $params = []) {
        $stmt = $this->executeQuery($sql, $params);
        return $stmt ? $this->conn->lastInsertId() : false;
    }

    /**
     * Update record
     */
    public function update($sql, $params = []) {
        $stmt = $this->executeQuery($sql, $params);
        return $stmt ? $stmt->rowCount() : false;
    }

    /**
     * Delete record
     */
    public function delete($sql, $params = []) {
        $stmt = $this->executeQuery($sql, $params);
        return $stmt ? $stmt->rowCount() : false;
    }

    /**
     * Check if record exists
     */
    public function exists($sql, $params = []) {
        $result = $this->getSingle($sql, $params);
        return !empty($result);
    }

    /**
     * Count records
     */
    public function count($sql, $params = []) {
        $result = $this->getSingle($sql, $params);
        return $result ? (int)$result['count'] : 0;
    }

    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit() {
        return $this->conn->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->conn->rollback();
    }
}

// Global database instance
$database = new Database();
$db = $database->getConnection();
?>