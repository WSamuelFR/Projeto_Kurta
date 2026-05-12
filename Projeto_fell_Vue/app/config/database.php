<?php

class Database {
    private $db_file;
    public $conn;

    public function __construct() {
        // SQLite database file path
        $this->db_file = __DIR__ . '/../database/kurta.db';
    }

    public function getConnection() {
        $this->conn = null;
        try {
            // Conexão via SQLite
            $dsn = "sqlite:" . $this->db_file;
            $this->conn = new PDO($dsn);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Ativa chaves estrangeiras no SQLite
            $this->conn->exec("PRAGMA foreign_keys = ON;");
            
        } catch(PDOException $exception) {
            echo "Erro de Conexão SQLite: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>
