<?php
class ClanModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create a clan and assign creator as 'rei'
     */
    public function createClan($name, $description, $visibility, $creator_id, $pic) {
        try {
            $this->conn->beginTransaction();
            
            // 1. Insert Clan
            $sql = "INSERT INTO clan (name_clan, description, visibility, clan_pic) VALUES (:n, :d, :v, :p)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':n', $name);
            $stmt->bindParam(':d', $description);
            $stmt->bindParam(':v', $visibility);
            $stmt->bindParam(':p', $pic);
            $stmt->execute();
            
            $clanId = $this->conn->lastInsertId();
            
            // 2. Insert Membership (Rei)
            $sqlRole = "INSERT INTO clan_member (clan_id, user_id, role) VALUES (:c, :u, 'rei')";
            $stmtRole = $this->conn->prepare($sqlRole);
            $stmtRole->bindParam(':c', $clanId);
            $stmtRole->bindParam(':u', $creator_id);
            $stmtRole->execute();
            
            $this->conn->commit();
            
            return $clanId;
        } catch(PDOException $e) {
            $this->conn->rollBack();
            error_log("Error creating clan: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get basic info about a clan
     */
    public function getClanInfo($clanId) {
        try {
            $sql = "SELECT * FROM clan WHERE clan_id = :c LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':c', $clanId);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) { return false; }
    }

    /**
     * Verify user role in a clan
     */
    public function getUserRole($clanId, $userId) {
        try {
            $sql = "SELECT role FROM clan_member WHERE clan_id = :c AND user_id = :u LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':c', $clanId);
            $stmt->bindParam(':u', $userId);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $row['role'] : null;
        } catch(PDOException $e) { return null; }
    }

    /**
     * Get all members with user details
     */
    public function getMembers($clanId) {
        try {
            $sql = "SELECT cm.role, cm.joined_at, u.user_id, u.first_name, u.last_name, u.profile_pic 
                    FROM clan_member cm
                    JOIN user u ON cm.user_id = u.user_id
                    WHERE cm.clan_id = :c
                    ORDER BY 
                       CASE role
                         WHEN 'rei' THEN 1
                         WHEN 'lider' THEN 2
                         ELSE 3
                       END, cm.joined_at ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':c', $clanId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) { return []; }
    }

    /**
     * Add member to clan
     */
    public function addMember($clanId, $userId, $role = 'aldeao') {
        try {
            $sql = "INSERT INTO clan_member (clan_id, user_id, role) VALUES (:c, :u, :r)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':c', $clanId);
            $stmt->bindParam(':u', $userId);
            $stmt->bindParam(':r', $role);
            return $stmt->execute();
        } catch(PDOException $e) { return false; }
    }

    /**
     * Change a member's role (Rei transferring power, or promoting leaders)
     */
    public function updateRole($clanId, $userId, $newRole) {
        try {
            $sql = "UPDATE clan_member SET role = :r WHERE clan_id = :c AND user_id = :u";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':r', $newRole);
            $stmt->bindParam(':c', $clanId);
            $stmt->bindParam(':u', $userId);
            return $stmt->execute();
        } catch(PDOException $e) { return false; }
    }

    /**
     * Remove member from clan
     */
    public function removeMember($clanId, $userId) {
        try {
            $sql = "DELETE FROM clan_member WHERE clan_id = :c AND user_id = :u AND role != 'rei'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':c', $clanId);
            $stmt->bindParam(':u', $userId);
            return $stmt->execute();
        } catch(PDOException $e) { return false; }
    }

    /**
     * Re-assign King if current king steps down.
     */
    public function kingAbdicate($clanId, $kingId, $targetUserId = null) {
        try { $this->conn->beginTransaction();
            if ($targetUserId) {
                // Demote old king to aldeao, promote new to rei
                $this->updateRole($clanId, $kingId, 'aldeao');
                $this->updateRole($clanId, $targetUserId, 'rei');
            } else {
                // Find oldest leader
                $sql = "SELECT user_id FROM clan_member WHERE clan_id = :c AND role = 'lider' ORDER BY joined_at ASC LIMIT 1";
                $stmt = $this->conn->prepare($sql); $stmt->execute([':c' => $clanId]);
                $next = $stmt->fetchColumn();
                if ($next) {
                    $this->updateRole($clanId, $kingId, 'aldeao');
                    $this->updateRole($clanId, $next, 'rei');
                } else {
                    // No leaders exist, find oldest aldeão
                     $sql2 = "SELECT user_id FROM clan_member WHERE clan_id = :c AND role = 'aldeao' AND user_id != :u ORDER BY joined_at ASC LIMIT 1";
                     $stmt2 = $this->conn->prepare($sql2); $stmt2->execute([':c' => $clanId, ':u' => $kingId]);
                     $nextO = $stmt2->fetchColumn();
                     if ($nextO) {
                         $this->updateRole($clanId, $kingId, 'aldeao');
                         $this->updateRole($clanId, $nextO, 'rei');
                     }
                     // If he is alone, he can't abdicate unless leaving(?)
                }
            }
        $this->conn->commit(); return true;
        } catch(PDOException $e) { $this->conn->rollBack(); return false; }
    }
}
?>
