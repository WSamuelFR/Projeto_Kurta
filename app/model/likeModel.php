<?php
class LikeModel {
    private $conn;
    private $table_name = "likes";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Alterna o status da curtida (se existir remove, se não existir adiciona)
     */
    public function toggleLike($userId, $feelingId) {
        try {
            // Verificar se já existe a curtida
            $sqlCheck = "SELECT like_id FROM " . $this->table_name . " WHERE user_id = :userid AND feeling_id = :feelingid";
            $stmtCheck = $this->conn->prepare($sqlCheck);
            $stmtCheck->bindParam(':userid', $userId, PDO::PARAM_INT);
            $stmtCheck->bindParam(':feelingid', $feelingId, PDO::PARAM_INT);
            $stmtCheck->execute();

            if ($stmtCheck->rowCount() > 0) {
                // Removendo curtida
                $sqlDel = "DELETE FROM " . $this->table_name . " WHERE user_id = :userid AND feeling_id = :feelingid";
                $stmtDel = $this->conn->prepare($sqlDel);
                $stmtDel->bindParam(':userid', $userId, PDO::PARAM_INT);
                $stmtDel->bindParam(':feelingid', $feelingId, PDO::PARAM_INT);
                $stmtDel->execute();
                return ['action' => 'unliked'];
            } else {
                // Adicionando curtida
                $sqlAdd = "INSERT INTO " . $this->table_name . " (user_id, feeling_id) VALUES (:userid, :feelingid)";
                $stmtAdd = $this->conn->prepare($sqlAdd);
                $stmtAdd->bindParam(':userid', $userId, PDO::PARAM_INT);
                $stmtAdd->bindParam(':feelingid', $feelingId, PDO::PARAM_INT);
                $stmtAdd->execute();
                return ['action' => 'liked'];
            }
        } catch (PDOException $e) {
            error_log("Kurta SQL Erro de Like: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retorna o total atualizado de curtidas de um post
     */
    public function getLikesCount($feelingId) {
        try {
            $sql = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE feeling_id = :feelingid";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':feelingid', $feelingId, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) $row['total'];
        } catch (PDOException $e) {
            return 0;
        }
    }
}
?>
