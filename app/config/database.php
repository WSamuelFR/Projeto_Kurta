<?php

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    public $conn;

    public function __construct() {
        $env_path = __DIR__ . '/../../.env';
        if (file_exists($env_path)) {
            $env = parse_ini_file($env_path);
            if ($env) {
                $this->host = $env['DB_HOST'] ?? 'localhost';
                $this->db_name = $env['DB_NAME'] ?? 'kurta';
                $this->username = $env['DB_USER'] ?? 'root';
                $this->password = $env['DB_PASS'] ?? '';
                $this->port = $env['DB_PORT'] ?? '3306';
            }
        } else {
            // Fallback parameters if .env is missing
            $this->host = 'localhost';
            $this->db_name = 'kurta';
            $this->username = 'root';
            $this->password = '';
            $this->port = '3306';
        }
    }

    public function getConnection() {
        $this->conn = null;
        try {
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Erro de Conexão: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>
