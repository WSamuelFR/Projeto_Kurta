<?php
// Garantir que a sessão existe para ser destruída
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Limpa todas as variáveis de sessão
$_SESSION = array();

// Se desejar destruir o cookie da sessão também (opcional mas recomendado)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destrói a sessão no servidor
session_destroy();

// Redireciona para a tela de login (index.php na raiz pública)
header("Location: ../../../public/index.php");
exit();
?>
