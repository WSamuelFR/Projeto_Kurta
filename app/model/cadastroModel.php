<?php
require_once __DIR__ . '/../config/database.php';

class CadastroModel {
    private $conn;
    private $table_user = "user";
    private $table_login = "login";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Verifica se o e-mail informado já existe no banco de dados.
     */
    public function emailExists($email) {
        $query = "SELECT user_id FROM " . $this->table_user . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Executa a transação para inserir na tabela user e login integrados.
     */
    public function registerUser($firstName, $lastName, $phone, $email, $hashedPassword) {
        try {
            // Inicia a transação PDO
            $this->conn->beginTransaction();

            // Insere os dados base em `user`
            $queryUser = "INSERT INTO " . $this->table_user . " 
                          (first_name, last_name, phone, email) 
                          VALUES (:first_name, :last_name, :phone, :email)";
            $stmtUser = $this->conn->prepare($queryUser);
            
            // Tratamento anti xss local
            $firstName = htmlspecialchars(strip_tags($firstName));
            $lastName = htmlspecialchars(strip_tags($lastName ?? ''));
            $phone = htmlspecialchars(strip_tags($phone ?? ''));
            $email = htmlspecialchars(strip_tags($email));
            
            // Tratar campos anuláveis
            $lastNameValue = !empty($lastName) ? $lastName : null;
            $phoneValue = !empty($phone) ? $phone : null;

            $stmtUser->bindParam(":first_name", $firstName);
            $stmtUser->bindParam(":last_name", $lastNameValue);
            $stmtUser->bindParam(":phone", $phoneValue);
            $stmtUser->bindParam(":email", $email);
            
            if (!$stmtUser->execute()) {
                throw new Exception("Falha ao registrar dados do usuário.");
            }

            // Pega o ID gerado para este user inserido no log
            $userId = $this->conn->lastInsertId();

            // Insere as credenciais de autenticação em `login` atrelada ao id
            $queryLogin = "INSERT INTO " . $this->table_login . " 
                           (user, password, level_acess) 
                           VALUES (:user, :password, 'user')";
            $stmtLogin = $this->conn->prepare($queryLogin);
            
            $stmtLogin->bindParam(":user", $userId);
            $stmtLogin->bindParam(":password", $hashedPassword);

            if (!$stmtLogin->execute()) {
                throw new Exception("Falha ao registrar senhas de acesso.");
            }

            // Confirma todas as transações (commit)
            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            // Reverte (Rollback) em caso de falha em qualquer uma das queries
            $this->conn->rollBack();
            return false;
        }
    }
}
?>
