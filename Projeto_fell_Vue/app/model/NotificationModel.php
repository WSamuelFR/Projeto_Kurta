<?php
class NotificationModel {
    private $conn;
    private $table_name = "notification";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create a new notification
     * type: 'like', 'comment', etc.
     */
    public function createNotification($userId, $senderId, $type, $referenceId) {
        if ($userId == $senderId) return false; // Don't notify yourself
        try {
            // Anti-spam trigger (e.g if like was removed and re-added quickly we don't spam if one already exists unread)
            $sqlCheck = "SELECT notif_id FROM " . $this->table_name . " WHERE user_id = :u AND sender_id = :s AND notif_type = :t AND reference_id = :r AND is_read = 0";
            $stmtCheck = $this->conn->prepare($sqlCheck);
            $stmtCheck->bindParam(':u', $userId);
            $stmtCheck->bindParam(':s', $senderId);
            $stmtCheck->bindParam(':t', $type);
            $stmtCheck->bindParam(':r', $referenceId);
            $stmtCheck->execute();
            if ($stmtCheck->rowCount() > 0) return true; // Already exists an active notif for this action

            $sql = "INSERT INTO " . $this->table_name . " (user_id, sender_id, notif_type, reference_id) VALUES (:u, :s, :t, :r)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':u', $userId);
            $stmt->bindParam(':s', $senderId);
            $stmt->bindParam(':t', $type);
            $stmt->bindParam(':r', $referenceId);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error creating notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete notification on unlike/uncomment to clean up UI
     */
    public function removeNotification($userId, $senderId, $type, $referenceId) {
        try {
            $sql = "DELETE FROM " . $this->table_name . " WHERE user_id = :u AND sender_id = :s AND notif_type = :t AND reference_id = :r";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':u', $userId);
            $stmt->bindParam(':s', $senderId);
            $stmt->bindParam(':t', $type);
            $stmt->bindParam(':r', $referenceId);
            return $stmt->execute();
        } catch (PDOException $e) { return false; }
    }

    public function getUnreadCount($userId) {
        try {
            $sql = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE user_id = :u AND is_read = 0";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':u', $userId);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) $row['total'];
        } catch (PDOException $e) { return 0; }
    }

    public function getNotifications($userId, $limit = 20) {
        try {
            $sql = "SELECT n.notif_id, n.notif_type, n.reference_id, n.is_read, n.created_at,
                           u.first_name, u.last_name, u.profile_pic
                    FROM " . $this->table_name . " n
                    JOIN user u ON n.sender_id = u.user_id
                    WHERE n.user_id = :u
                    ORDER BY n.created_at DESC LIMIT :limit";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':u', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function markAllAsRead($userId) {
        try {
            $sql = "UPDATE " . $this->table_name . " SET is_read = 1 WHERE user_id = :u AND is_read = 0";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':u', $userId);
            return $stmt->execute();
        } catch (PDOException $e) { return false; }
    }
}
?>
