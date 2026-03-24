<?php

class Criptography {
    
    /**
     * Gera o hash seguro de uma senha
     * @param string $password Senha em texto plano
     * @return string Senha com hash criptografado
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Verifica se a senha fornecida confere com o hash no banco
     * @param string $password Senha em texto plano
     * @param string $hash Senha salva no banco de dados com hash
     * @return bool
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}
?>
