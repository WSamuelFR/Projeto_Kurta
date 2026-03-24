<?php
class friendModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Busca usuários parecidos magicamente via barra, ignorando a si mesmo e exibindo array JSON limpo
    public function searchUsers($searchTerm, $myUserId) {
        try {
            $term = "%{$searchTerm}%";
            $sql = "SELECT user_id, first_name, last_name, profile_pic FROM user 
                    WHERE (first_name LIKE :term OR last_name LIKE :term OR email LIKE :term) 
                    AND user_id != :myid LIMIT 10";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':term', $term, PDO::PARAM_STR);
            $stmt->bindParam(':myid', $myUserId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("FriendSearch SQL Erro: " . $e->getMessage());
            return false;
        }
    }

    // Córtex que Impede convites duplicados por Invasão e tenta inserir relacionamento pending novo
    public function sendInvite($senderId, $receiverId) {
        try {
            $sqlCheck = "SELECT friendship_id FROM friendship WHERE (sender_id = :s1 AND receiver_id = :r1) OR (sender_id = :r2 AND receiver_id = :s2)";
            $stmtCheck = $this->conn->prepare($sqlCheck);
            $stmtCheck->bindParam(':s1', $senderId, PDO::PARAM_INT); $stmtCheck->bindParam(':r1', $receiverId, PDO::PARAM_INT);
            $stmtCheck->bindParam(':s2', $senderId, PDO::PARAM_INT); $stmtCheck->bindParam(':r2', $receiverId, PDO::PARAM_INT);
            $stmtCheck->execute();

            if($stmtCheck->rowCount() > 0) return 'exists'; // Já existe relacionamento trancado ou ativo previamente

            $sqlInsert = "INSERT INTO friendship (sender_id, receiver_id, status) VALUES (:send, :recv, 'pending')";
            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->bindParam(':send', $senderId, PDO::PARAM_INT);
            $stmtInsert->bindParam(':recv', $receiverId, PDO::PARAM_INT);
            $stmtInsert->execute();
            return 'success';
        } catch (PDOException $e) {
            error_log("FriendInsert Sql Erro: " . $e->getMessage());
            return 'error';
        }
    }

    // Inbox Notification Join: Extrai dezenas de informações preciosas do emissor cruzadas à nossa Receiver Table baseados no nosso Token
    public function getPendingRequests($myUserId) {
        try {
            $sql = "SELECT f.friendship_id, f.created_at, u.user_id as sender_id, u.first_name, u.last_name, u.profile_pic 
                    FROM friendship f 
                    JOIN user u ON f.sender_id = u.user_id 
                    WHERE f.receiver_id = :myid AND f.status = 'pending' 
                    ORDER BY f.created_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':myid', $myUserId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Notification Inbox Erro: " . $e->getMessage());
            return false;
        }
    }

    // Responder ou Escomungar Relacionamento limitando a propriedade relacional de quem recebe
    public function respondInvite($friendshipId, $myUserId, $action) {
        try {
            // WHERE receiver = me (Anti-hacker para ninguém aprovar seus próprios despachos pendentes interceptando json falso)
            $sql = "UPDATE friendship SET status = :status WHERE friendship_id = :fid AND receiver_id = :myid AND status = 'pending'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':status', $action, PDO::PARAM_STR);
            $stmt->bindParam(':fid', $friendshipId, PDO::PARAM_INT);
            $stmt->bindParam(':myid', $myUserId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Decision Update Sql Erro: " . $e->getMessage());
            return false;
        }
    }

    // Extrator de Cards UI (Meus Amigos Aprovados ou de Visitados) (Array Builder)
    public function getMyFriends($userId) {
        try {
            $sql = "SELECT u.user_id, u.first_name, u.last_name, u.profile_pic 
                    FROM friendship f
                    JOIN user u ON (u.user_id = f.sender_id OR u.user_id = f.receiver_id) 
                    WHERE (f.sender_id = :id OR f.receiver_id = :id2) 
                    AND f.status = 'accepted' 
                    AND u.user_id != :id3 
                    ORDER BY u.first_name ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':id2', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':id3', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Friends Erro Pdo: " . $e->getMessage());
            return false;
        }
    }

    // Leitor de Relacionamento (Visitor Mode Button Painter) Retorna states Enums textuais p/ IFs Frontend
    public function getFriendshipStatus($userA, $userB) {
        try {
            $sql = "SELECT sender_id, receiver_id, status FROM friendship 
                    WHERE (sender_id = :a1 AND receiver_id = :b1) 
                    OR (sender_id = :b2 AND receiver_id = :a2)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':a1', $userA, PDO::PARAM_INT); $stmt->bindParam(':b1', $userB, PDO::PARAM_INT);
            $stmt->bindParam(':b2', $userB, PDO::PARAM_INT); $stmt->bindParam(':a2', $userA, PDO::PARAM_INT);
            $stmt->execute();
            
            if($stmt->rowCount() == 0) return 'none'; // Isolados, sem laços.
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if($row['status'] == 'accepted') return 'accepted';
            if($row['status'] == 'rejected') return 'rejected'; // Locked/Ignorado
            
            // É status pendente. Mas QUEM ESTAR PENDENTE? (Eu enviei ou Enviaram pra mim?)
            if($row['sender_id'] == $userA) return 'pending_sent';
            return 'pending_received';

        } catch (PDOException $e) {
            error_log("Get Friendship Status Tracker Erro: " . $e->getMessage());
            return 'error';
        }
    }

    // Exterminador Impiedoso de Database Row (Desfazer Amizade Fatal sem Bkp SQL)
    public function removeFriend($userA, $userB) {
        try {
            $sql = "DELETE FROM friendship 
                    WHERE ((sender_id = :a1 AND receiver_id = :b1) OR (sender_id = :b2 AND receiver_id = :a2)) 
                    AND status = 'accepted'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':a1', $userA, PDO::PARAM_INT); $stmt->bindParam(':b1', $userB, PDO::PARAM_INT);
            $stmt->bindParam(':b2', $userB, PDO::PARAM_INT); $stmt->bindParam(':a2', $userA, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Unfriend Lethal Erro Pdo: " . $e->getMessage());
            return false;
        }
    }
}
?>
