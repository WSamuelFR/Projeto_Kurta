<?php

class EmailVerifier {
    
    /**
     * Sanitiza e valida o formato do e-mail usando os filtros nativos do PHP
     * @param string $email email em texto 
     * @return bool|string Retorna o e-mail validado ou falso num e-mail mal formulado
     */
    public static function validateAndSanitize($email) {
        $cleanEmail = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        
        if (filter_var($cleanEmail, FILTER_VALIDATE_EMAIL)) {
            return $cleanEmail;
        }
        
        return false;
    }
}
?>
