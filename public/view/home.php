<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$pageTitle = "Home Galáctica";
$extraScript = "../assets/js/home.js";

include 'header/header.php';
?>

<div class="profile-container" style="max-width: 800px; margin: 0 auto; padding-top: 2rem;">
    
    <!-- Abas Globais Inferiores (Mesmo Padrão do Perfil) -->
    <nav class="profile-tabs" style="justify-content: center; gap: 30px; margin-bottom: 2.5rem; padding-bottom: 10px; border-bottom: 1px solid rgba(255,255,255,0.05);">
        <button class="tab-btn active" data-target="tab-principal" style="font-size: 1.15rem; font-weight: 500; letter-spacing: 0.5px;">🌍 Principal</button>
        <button class="tab-btn" data-target="tab-amigos" style="font-size: 1.15rem; font-weight: 500; letter-spacing: 0.5px;">👥 Amigos</button>
        <button class="tab-btn" data-target="tab-clas" style="font-size: 1.15rem; font-weight: 500; letter-spacing: 0.5px;">🛡️ Clãs</button>
    </nav>

    <!-- Corpos Condicionais das Abas -->
    <div class="profile-body" style="padding-bottom: 4rem;">
        
        <!-- Tab 01: Principal (Engajamento Global) -->
        <div id="tab-principal" class="tab-content active">
            <h2 style="color:var(--text-main); margin-bottom: 2rem; text-align:center; font-weight: 300;">Explorar Rede Conectada</h2>
            <div id="feedContainerWrapper">
                <!-- Loader Inicial UI -->
                <div class="glass-card feed-box">
                    <p class="empty-state">Sincronizando mentes pela vasta rede...</p>
                </div>
            </div>
        </div>

        <!-- Tab 02: Amigos (Recentes) -->
        <div id="tab-amigos" class="tab-content">
            <h2 style="color:var(--text-main); margin-bottom: 2rem; text-align:center; font-weight: 300;">Seu Círculo Social</h2>
            <div id="feedFriendsContainer">
                <div class="glass-card feed-box">
                    <p class="empty-state">Buscando as novidades restritas da sua lista de amigos...</p>
                </div>
            </div>
        </div>

        <!-- Tab 03: Clãs -->
        <div id="tab-clas" class="tab-content">
            <div class="glass-card full-box">
                <div class="pill-container" style="justify-content: center; margin-bottom: 20px;">
                    <button class="btn-pill active" data-filter="meus-clas">Meus Clãs</button>
                    <button class="btn-pill" data-filter="participei">Clãs que Faço Parte</button>
                    <button class="btn-pill" data-filter="todos">Explorar Clãs</button>
                </div>
                <div class="table-responsive">
                    <table class="glass-table">
                        <thead>
                            <tr>
                                <th>Nome do Clã</th>
                                <th>Participantes</th>
                                <th>Ação Direta</th>
                            </tr>
                        </thead>
                        <tbody id="clanTableBody">
                            <tr><td colspan="3" style="text-align: center; color: rgba(255,255,255,0.5);">Carregando Dados...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include 'header/footer.php'; ?>
