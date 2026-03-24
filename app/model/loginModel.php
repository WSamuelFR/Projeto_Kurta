<?php
require_once __DIR__ . '/../config/database.php';

class LoginModel {
    private $conn;
    private $table_name = "user";
    private $login_table = "login";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Busca usuário e login baseado no email fornecido
     * @param string $email email submetido na tentativa de login
     * @return array|false array contendo campos ou falso se não existir
     */
    public function getUserByEmail($email) {
        // Query paramterizada via INNER JOIN para trazer informações das tabelas user e login unidas
        $query = "SELECT u.user_id, u.first_name, u.last_name, u.email, u.profile_pic, l.password, l.level_acess 
                  FROM " . $this->table_name . " u 
                  INNER JOIN " . $this->login_table . " l ON u.user_id = l.user 
                  WHERE u.email = :email LIMIT 0,1";
                  
        $stmt = $this->conn->prepare($query);
        
        // Limpa a string evitando injeções
        $email = htmlspecialchars(strip_tags($email));
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }
}
?>
