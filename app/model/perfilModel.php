<?php
require_once __DIR__ . '/../config/database.php';

class PerfilModel {
    private $conn;
    private $table_user = "user";
    private $table_login = "login";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getUserData($userId) {
        $query = "SELECT u.*, l.level_acess 
                  FROM " . $this->table_user . " u
                  JOIN " . $this->table_login . " l ON u.user_id = l.user
                  WHERE u.user_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProfile($userId, $data) {
        try {
            $query = "UPDATE " . $this->table_user . " SET 
                      first_name = :first, 
                      last_name = :last, 
                      phone = :phone 
                      WHERE user_id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':first', $data['first_name']);
            $stmt->bindParam(':last', $data['last_name']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':id', $userId);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao atualizar perfil: " . $e->getMessage());
            return false;
        }
    }

    public function updateProfilePic($userId, $path) {
        $query = "UPDATE " . $this->table_user . " SET profile_pic = :path WHERE user_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':path', $path);
        $stmt->bindParam(':id', $userId);
        return $stmt->execute();
    }

    public function updateWallpaperPic($userId, $path) {
        $query = "UPDATE " . $this->table_user . " SET wallpaper_pic = :path WHERE user_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':path', $path);
        $stmt->bindParam(':id', $userId);
        return $stmt->execute();
    }

    public function updatePassword($userId, $newPassword) {
        $query = "UPDATE " . $this->table_login . " SET password = :pass WHERE user = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':pass', $newPassword);
        $stmt->bindParam(':id', $userId);
        return $stmt->execute();
    }
}
