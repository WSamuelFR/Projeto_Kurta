<?php
class commentModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Injeção de Comentário Limitada a 1500 letras com hierarquia nula (nativo post) ou número (reply)
    public function addComment($feelingId, $userId, $text, $parentId = null) {
        try {
            $sql = "INSERT INTO coments (feeling, user, coment, parent_id) VALUES (:f, :u, :t, :p)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':f', $feelingId, PDO::PARAM_INT);
            $stmt->bindParam(':u', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':t', $text, PDO::PARAM_STR);
            if ($parentId === null) {
                $stmt->bindValue(':p', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':p', $parentId, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Comment Add Erro PDO: " . $e->getMessage());
            return false;
        }
    }

    // Obtenção Massiva de Comentários de um Feeling (Traz Parente, Avatar e Name) em Ordem Cronológica
    public function getCommentsByFeeling($feelingId) {
        try {
            $sql = "SELECT c.coment_id, c.coment, c.parent_id, c.created_at, 
                           u.user_id, u.first_name, u.last_name, u.profile_pic,
                           f.user as post_owner_id
                    FROM coments c
                    JOIN user u ON c.user = u.user_id
                    JOIN feeling f ON c.feeling = f.feeling_id
                    WHERE c.feeling = :f
                    ORDER BY c.created_at ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':f', $feelingId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Load Comences Erro PDO: " . $e->getMessage());
            return false;
        }
    }

    // Edição Limitada (Apenas o Próprio Dono do Comentário)
    public function editComment($commentId, $userId, $newText) {
        try {
            $sql = "UPDATE coments SET coment = :text WHERE coment_id = :cid AND user = :uid";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':text', $newText, PDO::PARAM_STR);
            $stmt->bindParam(':cid', $commentId, PDO::PARAM_INT);
            $stmt->bindParam(':uid', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Edit Comment Erro PDO: " . $e->getMessage());
            return false;
        }
    }

    // Controle Duplo de Delete: Apaga o Comentário se você for quem DIGITOU ele, ou se VOCÊ FOR O DONO DA CONTA ONDE POSTARAM ISSO!
    public function deleteComment($commentId, $reclamanteId) {
        try {
            // Validamos primeiro quem é o Dono do Comentário e o Dono da Postagem onde foi feito!
            $sqlCheck = "SELECT c.user as autor_comentario, f.user as autor_postagem 
                         FROM coments c
                         JOIN feeling f ON c.feeling = f.feeling_id
                         WHERE c.coment_id = :cid";
            $stmtCheck = $this->conn->prepare($sqlCheck);
            $stmtCheck->bindParam(':cid', $commentId, PDO::PARAM_INT);
            $stmtCheck->execute();
            $data = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            
            if (!$data) return false;

            // Condição do Firewall de Acesso ao Delete Córtex (Moderação ou Autoria):
            if ($data['autor_comentario'] == $reclamanteId || $data['autor_postagem'] == $reclamanteId) {
                // Deleta. (Sendo CASCADE na Root, todos os sub-respostas [filhos] morrerão também sozinhos no MySQL).
                $sqlDel = "DELETE FROM coments WHERE coment_id = :cid";
                $stmtDel = $this->conn->prepare($sqlDel);
                $stmtDel->bindParam(':cid', $commentId, PDO::PARAM_INT);
                $stmtDel->execute();
                return $stmtDel->rowCount() > 0;
            }

            // Não autorizado
            return false;
        } catch (PDOException $e) {
            error_log("Delete Comment Erro Córtex Firewal: " . $e->getMessage());
            return false;
        }
    }
}
?>
