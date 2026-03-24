<?php
// Certifica-se de que a sessão foi iniciada se ainda não estiver (Proteção extra)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$navUserName = $_SESSION['user_name'] ?? 'Visitante';
$navUserProfile = $_SESSION['profile_pic'] ?? 'assets/files/default_avatar.png';

// Fallback logic para caso a imagem padrão não esteja na pasta
if (strpos($navUserProfile, 'http') === 0) {
    $profilePicPath = $navUserProfile;
} else {
    $profilePicPath = '../' . $navUserProfile;
    $localFilePath = __DIR__ . '/../../' . $navUserProfile;
    if (!file_exists($localFilePath)) {
        $encodedName = urlencode($navUserName);
        $profilePicPath = "https://ui-avatars.com/api/?name={$encodedName}&background=0ea5e9&color=fff&rounded=true&bold=true";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - feel.it' : 'feel.it' ?></title>
    <!-- Fonts Base -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/perfil.css"> 
</head>
<body>
    <header class="main-header glass-nav">
        <div class="header-left">
            <a href="perfil.php" class="user-greeting" style="display: flex; align-items: center; text-decoration: none; gap: 10px;">
                <img src="<?= htmlspecialchars($profilePicPath) ?>" alt="Foto de Perfil" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover;">
                <span>Olá, <?= htmlspecialchars($navUserName) ?></span>
            </a>
        </div>
        <div class="header-center">
            <a href="home.php" class="brand-logo">feel.it</a>
        </div>
        <div class="header-right" style="display: flex; align-items: center; gap: 20px;">
            <div style="position: relative;">
                <button type="button" id="btnSinoNotificacao">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bell"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                    <span id="badgeSinoAlert" style="display: none; position: absolute; top: -5px; right: -5px; background: #ef4444; color: white; border-radius: 50%; font-size: 0.65rem; width: 16px; height: 16px; align-items: center; justify-content: center; font-weight: bold; box-shadow: 0 0 5px rgba(239, 68, 68, 0.5);">0</span>
                </button>
                
                <!-- Dropdown das Notificações -->
                <div id="notificationsDropdown" class="glass-dropdown">
                    <div class="dropdown-header">Notificações</div>
                    <div id="notificationsList" class="dropdown-body">
                        <!-- Carregado via JS -->
                    </div>
                </div>
            </div>
            <a href="../../app/controller/auth_controller/logout.php" class="btn-logout" title="Encerrar Sessão">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                <span>Sair</span>
            </a>
        </div>
    </header>

    <main class="main-content">
    <script src="../assets/js/notifications.js" defer></script>
    <script src="../assets/js/clan_creator.js" defer></script>
