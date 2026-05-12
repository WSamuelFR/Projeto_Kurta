<?php
class feelingModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function createFeeling($userId, $bodyText, $visibility, $claId) {
        try {
            // Prepared Query Protegida limitador bypass
            $sql = "INSERT INTO feeling (user, feeling, visibility, cla_id) VALUES (:user, :feeling, :visibility, :cla_id)";
            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindParam(':user', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':feeling', $bodyText, PDO::PARAM_STR);
            $stmt->bindParam(':visibility', $visibility, PDO::PARAM_STR);

            // Filtro PDO flexível caso não tenha clã definido Options
            if ($claId === null) {
                $stmt->bindValue(':cla_id', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':cla_id', $claId, PDO::PARAM_INT);
            }

            return $stmt->execute();

        } catch (PDOException $e) {
            // Logs ocultos para dev admin
            error_log("Kurta/Feel.it DB Crash ao salvar Feeling: " . $e->getMessage());
            return false;
        }
    }

    public function getMyFeelings($targetId, $visitorId = null) {
        try {
            if ($visitorId === null) $visitorId = $targetId;

            if ($targetId === $visitorId) {
                $sql = "SELECT f.feeling_id, f.feeling, f.visibility, f.created_at, 
                               u.first_name, u.last_name, u.profile_pic,
                               (SELECT COUNT(*) FROM likes WHERE feeling_id = f.feeling_id) as likes_count,
                               (SELECT COUNT(*) FROM coments WHERE feeling = f.feeling_id) as comments_count,
                               (SELECT COUNT(*) FROM likes WHERE feeling_id = f.feeling_id AND user_id = :visitor) as user_has_liked
                        FROM feeling f 
                        JOIN user u ON f.user = u.user_id 
                        WHERE f.user = :userid 
                        ORDER BY f.created_at DESC";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':userid', $targetId, PDO::PARAM_INT);
                $stmt->bindParam(':visitor', $visitorId, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            require_once 'friendModel.php';
            $fm = new friendModel($this->conn);
            $status = $fm->getFriendshipStatus($targetId, $visitorId);
            
            if ($status === 'accepted') {
                $sql = "SELECT f.feeling_id, f.feeling, f.visibility, f.created_at, 
                               u.first_name, u.last_name, u.profile_pic,
                               (SELECT COUNT(*) FROM likes WHERE feeling_id = f.feeling_id) as likes_count,
                               (SELECT COUNT(*) FROM coments WHERE feeling = f.feeling_id) as comments_count,
                               (SELECT COUNT(*) FROM likes WHERE feeling_id = f.feeling_id AND user_id = :visitor) as user_has_liked
                        FROM feeling f 
                        JOIN user u ON f.user = u.user_id 
                        WHERE f.user = :userid AND f.visibility != 'private'
                        ORDER BY f.created_at DESC";
            } else {
                $sql = "SELECT f.feeling_id, f.feeling, f.visibility, f.created_at, 
                               u.first_name, u.last_name, u.profile_pic,
                               (SELECT COUNT(*) FROM likes WHERE feeling_id = f.feeling_id) as likes_count,
                               (SELECT COUNT(*) FROM coments WHERE feeling = f.feeling_id) as comments_count,
                               (SELECT COUNT(*) FROM likes WHERE feeling_id = f.feeling_id AND user_id = :visitor) as user_has_liked
                        FROM feeling f 
                        JOIN user u ON f.user = u.user_id 
                        WHERE f.user = :userid AND f.visibility = 'public'
                        ORDER BY f.created_at DESC";
            }
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':userid', $targetId, PDO::PARAM_INT);
            $stmt->bindParam(':visitor', $visitorId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erro de leitura dos Feeds Pdo: " . $e->getMessage());
            return false;
        }
    }

    public function deleteFeeling($feelingId, $userId) {
        try {
            // Trava de Sessão Direta Blindada (Apenas Autor apaga post)
            $sql = "DELETE FROM feeling WHERE feeling_id = :id AND user = :userid";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $feelingId, PDO::PARAM_INT);
            $stmt->bindParam(':userid', $userId, PDO::PARAM_INT);
            $stmt->execute();
            // Retorna Booleano V ou F se apagou
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Kurta SQL Erro de Delete: " . $e->getMessage());
            return false;
        }
    }

    public function updateFeelingBody($feelingId, $userId, $newBody) {
        try {
            // Trava dupla de Dono (Update só com o Token certo do Banco)
            $sql = "UPDATE feeling SET feeling = :body WHERE feeling_id = :id AND user = :userid";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':body', $newBody, PDO::PARAM_STR);
            $stmt->bindParam(':id', $feelingId, PDO::PARAM_INT);
            $stmt->bindParam(':userid', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Kurta SQL Erro ao Salvar Update: " . $e->getMessage());
            return false;
        }
    }

    public function updateFeelingPrivacy($feelingId, $userId) {
        try {
            // Um toggle mestre booleano encurtado: Se estiver private reseta para public. Se qlq outra coisa tranca pra private!
            $sql = "UPDATE feeling SET visibility = IF(visibility = 'private', 'public', 'private') WHERE feeling_id = :id AND user = :userid";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $feelingId, PDO::PARAM_INT);
            $stmt->bindParam(':userid', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Kurta SQL Erro de Toggle View: " . $e->getMessage());
            return false;
        }
    }

    public function getGlobalFeelings($visitorId = 0) {
        try {
            $sql = "SELECT f.feeling_id, f.feeling, f.visibility, f.created_at, f.user as user_id, f.cla_id,
                           u.first_name, u.last_name, u.profile_pic,
                           (SELECT COUNT(*) FROM likes WHERE feeling_id = f.feeling_id) as likes_count,
                           (SELECT COUNT(*) FROM coments WHERE feeling = f.feeling_id) as comments_count,
                           (SELECT COUNT(*) FROM likes WHERE feeling_id = f.feeling_id AND user_id = :visitor) as user_has_liked
                    FROM feeling f
                    JOIN user u ON f.user = u.user_id
                    WHERE f.visibility != 'private' AND f.cla_id IS NULL
                    ORDER BY ((SELECT COUNT(*) FROM likes WHERE feeling_id = f.feeling_id) + (SELECT COUNT(*) FROM coments WHERE feeling = f.feeling_id)) DESC, f.created_at DESC
                    LIMIT 50";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':visitor', $visitorId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Global Feed Erro Pdo: " . $e->getMessage());
            return false;
        }
    }

    public function getFriendsFeelings($visitorId) {
        try {
            // Traz apenas das amizades confirmadas (aceitas), ignorando a si mesmo, publicações globais de fora de clã
            $sql = "SELECT DISTINCT f.feeling_id, f.feeling, f.visibility, f.created_at, f.user as user_id, f.cla_id,
                           u.first_name, u.last_name, u.profile_pic,
                           (SELECT COUNT(*) FROM likes WHERE feeling_id = f.feeling_id) as likes_count,
                           (SELECT COUNT(*) FROM coments WHERE feeling = f.feeling_id) as comments_count,
                           (SELECT COUNT(*) FROM likes WHERE feeling_id = f.feeling_id AND user_id = :visitor) as user_has_liked
                    FROM feeling f
                    JOIN user u ON f.user = u.user_id
                    JOIN friendship fr ON (fr.sender_id = f.user OR fr.receiver_id = f.user)
                    WHERE (fr.sender_id = :visitor OR fr.receiver_id = :visitor) 
                      AND fr.status = 'accepted'
                      AND f.user != :visitor
                      AND f.visibility != 'private'
                      AND f.cla_id IS NULL
                    ORDER BY f.created_at DESC
                    LIMIT 50";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':visitor', $visitorId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Friends Feed Erro Pdo: " . $e->getMessage());
            return false;
        }
    }

    public function getClanFeelings($clanId, $visitorId = 0) {
        try {
            $sql = "SELECT f.feeling_id, f.feeling, f.visibility, f.created_at, f.user as user_id, f.cla_id,
                           u.first_name, u.last_name, u.profile_pic,
                           (SELECT COUNT(*) FROM likes WHERE feeling_id = f.feeling_id) as likes_count,
                           (SELECT COUNT(*) FROM coments WHERE feeling = f.feeling_id) as comments_count,
                           (SELECT COUNT(*) FROM likes WHERE feeling_id = f.feeling_id AND user_id = :visitor) as user_has_liked
                    FROM feeling f
                    JOIN user u ON f.user = u.user_id
                    WHERE f.cla_id = :clanid
                    ORDER BY f.created_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':visitor', $visitorId, PDO::PARAM_INT);
            $stmt->bindParam(':clanid', $clanId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Clan Feed Erro Pdo: " . $e->getMessage());
            return false;
        }
    }
}
?>
