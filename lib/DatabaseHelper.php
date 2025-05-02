<?php
/**
 * iwantto.be
 * Copyright (C) 2025 Xavier Dubois
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * Database Helper Class
 * 
 * Provides utility methods for database operations using PDO
 */
class DatabaseHelper {
    private static $instance = null;
    private $pdo = null;
    private $connected = false;
    private $errorMessage = '';
    
    /**
     * Private constructor to enforce singleton pattern
     */
    private function __construct() {
        $this->connect();
    }
    
    /**
     * Get singleton instance
     * 
     * @return DatabaseHelper
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Connect to the database using credentials
     * 
     * @return bool True if connection successful, false otherwise
     */
    private function connect() {
        // Load database configuration
        $credentialsFile = __DIR__ . '/../config/credentials.php';
        
        if (!file_exists($credentialsFile)) {
            $this->errorMessage = "Credentials file not found.";
            return false;
        }
        
        require_once($credentialsFile);
        
        if (!isset($dbConfig) || !is_array($dbConfig)) {
            $this->errorMessage = "Invalid credentials configuration.";
            return false;
        }
        
        try {
            $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['name']};charset=utf8mb4";
            $this->pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
            $this->connected = true;
            return true;
        } catch (PDOException $e) {
            $this->errorMessage = "Connection failed: " . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Check if connection is established
     * 
     * @return bool
     */
    public function isConnected() {
        return $this->connected;
    }
    
    /**
     * Get last error message
     * 
     * @return string
     */
    public function getErrorMessage() {
        return $this->errorMessage;
    }
    
    /**
     * Execute a query with parameters
     * 
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind
     * @return PDOStatement|false
     */
    public function query($query, $params = []) {
        if (!$this->isConnected()) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->errorMessage = "Query failed: " . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Fetch all rows from a query
     * 
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind
     * @return array|false Array of rows or false on failure
     */
    public function fetchAll($query, $params = []) {
        $stmt = $this->query($query, $params);
        
        if ($stmt === false) {
            return false;
        }
        
        return $stmt->fetchAll();
    }
    
    /**
     * Fetch a single row from a query
     * 
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind
     * @return array|false Single row or false on failure
     */
    public function fetchOne($query, $params = []) {
        $stmt = $this->query($query, $params);
        
        if ($stmt === false) {
            return false;
        }
        
        return $stmt->fetch();
    }
    
    /**
     * Fetch a single value from a query
     * 
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind
     * @return mixed|false Single value or false on failure
     */
    public function fetchValue($query, $params = []) {
        $stmt = $this->query($query, $params);
        
        if ($stmt === false) {
            return false;
        }
        
        return $stmt->fetchColumn();
    }
    
    /**
     * Insert a row into a table
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value pairs
     * @return int|false Last insert ID or false on failure
     */
    public function insert($table, $data) {
        if (!$this->isConnected() || empty($data)) {
            return false;
        }
        
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $query = "INSERT INTO `{$table}` ({$columns}) VALUES ({$placeholders})";
        
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(array_values($data));
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            $this->errorMessage = "Insert failed: " . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Update rows in a table
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value pairs
     * @param string $where WHERE clause (without "WHERE" keyword)
     * @param array $whereParams Parameters for WHERE clause
     * @return int|false Number of affected rows or false on failure
     */
    public function update($table, $data, $where, $whereParams = []) {
        if (!$this->isConnected() || empty($data)) {
            return false;
        }
        
        $setClauses = [];
        foreach ($data as $column => $value) {
            $setClauses[] = "{$column} = ?";
        }
        
        $query = "UPDATE `{$table}` SET " . implode(', ', $setClauses) . " WHERE {$where}";
        $params = array_merge(array_values($data), $whereParams);
        
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->errorMessage = "Update failed: " . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Delete rows from a table
     * 
     * @param string $table Table name
     * @param string $where WHERE clause (without "WHERE" keyword)
     * @param array $params Parameters for WHERE clause
     * @return int|false Number of affected rows or false on failure
     */
    public function delete($table, $where, $params = []) {
        if (!$this->isConnected()) {
            return false;
        }
        
        $query = "DELETE FROM `{$table}` WHERE {$where}";
        
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->errorMessage = "Delete failed: " . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Begin a transaction
     * 
     * @return bool
     */
    public function beginTransaction() {
        if (!$this->isConnected()) {
            return false;
        }
        
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit a transaction
     * 
     * @return bool
     */
    public function commit() {
        if (!$this->isConnected()) {
            return false;
        }
        
        return $this->pdo->commit();
    }
    
    /**
     * Rollback a transaction
     * 
     * @return bool
     */
    public function rollback() {
        if (!$this->isConnected()) {
            return false;
        }
        
        return $this->pdo->rollBack();
    }
    
    /**
     * Get the PDO instance directly for advanced usage
     * 
     * @return PDO|null
     */
    public function getPdo() {
        return $this->pdo;
    }
}
