<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

require_once '../../app/config/database.php';
require_once '../../app/model/friendModel.php';

$db = (new Database())->getConnection();
$friendModel = new friendModel($db);

$isVisitor = false;
$targetUserId = $_SESSION['user_id'];

// Se houver ID e não for o meu próprio Mestre da Sessão, ativa Modo Visitante (Read-Only Polimórfico)
if (isset($_GET['id']) && intval($_GET['id']) !== intval($_SESSION['user_id'])) {
    $isVisitor = true;
    $targetUserId = intval($_GET['id']);
}

// Fetch Extração de Dados do Usuário Alvo da Tela (Seja eu ou o Visitado no servidor)
$stmt = $db->prepare("SELECT first_name, last_name, profile_pic, wallpaper_pic FROM user WHERE user_id = :id");
$stmt->bindParam(':id', $targetUserId, PDO::PARAM_INT);
$stmt->execute();
$targetUserData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$targetUserData) {
    echo "Erro 404: Mente usuária não decifrada ou deletada da base de dados galáctica.";
    exit;
}

$userName = $targetUserData['first_name'] . ' ' . $targetUserData['last_name'];
$profilePic = $targetUserData['profile_pic'] ? $targetUserData['profile_pic'] : "../assets/files/default_avatar.png";
$wallpaperPic = $targetUserData['wallpaper_pic'] ? $targetUserData['wallpaper_pic'] : "../assets/files/default_wallpaper.png";

// Se for visitante Read-Only, varrer no MySQL status de amizade entre vocês
$friendStatus = 'none';
if ($isVisitor) {
    $friendStatus = $friendModel->getFriendshipStatus($_SESSION['user_id'], $targetUserId);
}

$pageTitle = $isVisitor ? "Perfil Visitante de " . htmlspecialchars($targetUserData['first_name']) : "Meu Perfil Doméstico";
$extraScript = "../assets/js/perfil.js";

// Exportamos variávies Globais pro Front-End JS saber se deve apagar ferramentas (⋮) UI de edição do feed
echo "<script>const IS_VISITOR = " . ($isVisitor ? 'true' : 'false') . "; const VISITOR_TARGET_ID = {$targetUserId};</script>";

include 'header/header.php';
?>

<div class="profile-container">
    <div class="profile-header glass-card">
        <!-- Wallpaper Background -->
        <div class="wallpaper-area" style="background-image: url('<?= $wallpaperPic ?>');">
            <?php if(!$isVisitor): ?>
            <button class="btn-edit-pic btn-edit-wallpaper" title="Alterar Papel de Parede">📷</button>
            <?php endif; ?>
        </div>
        
        <!-- Info Principal com Foto e Nome -->
        <div class="profile-info-area">
            <div class="avatar-wrapper">
                <img src="<?= $profilePic ?>" alt="Avatar" class="profile-avatar" onerror="this.src='https://ui-avatars.com/api/?background=4f46e5&color=fff&name=<?= urlencode($userName) ?>'">
                <?php if(!$isVisitor): ?>
                <button class="btn-edit-pic btn-edit-avatar" title="Alterar Foto de Perfil Alheio">📷</button>
                <?php endif; ?>
            </div>
            
            <div class="profile-details">
                <h1 class="profile-name"><?= htmlspecialchars($userName) ?></h1>
                <?php if(!$isVisitor): ?>
                <button class="btn-outline">Editar o perfil</button>
                <?php endif; ?>
            </div>
            
            <!-- CTAS e Botão Mestre Mutante Dinâmico Polimórfico (DRY Pattern) -->
            <div class="profile-actions">
                <?php if(!$isVisitor): ?>
                    <!-- MEU PERFIL NORMAL DASHBOARD -->
                    <button type="button" class="btn-primary" id="btnMasterAction">
                        <span class="btn-text" id="btnMasterText">📝 novo feeling!</span>
                    </button>
                <?php else: ?>
                    <!-- MODO VISITANTE READ-ONLY RELATION CTAS -->
                    <?php if($friendStatus === 'accepted'): ?>
                        <button type="button" class="btn-outline text-success" id="btnUnfriendAction" data-targetid="<?= $targetUserId ?>">
                            <span class="btn-text">✔️ Amigos (Desfazer)</span>
                        </button>
                    <?php elseif($friendStatus === 'pending_sent'): ?>
                        <button type="button" class="btn-outline" disabled>
                            <span class="btn-text">⏳ Convite Enviado</span>
                        </button>
                    <?php elseif($friendStatus === 'pending_received'): ?>
                        <button type="button" class="btn-primary" onclick="alert('Abra o Sino de Notificações no Header para responder formalmente!')">
                            <span class="btn-text">🔔 Responder Convite</span>
                        </button>
                    <?php else: ?>
                        <!-- GHOST ISOLADOS -->
                        <button type="button" class="btn-primary acao-enviar-convite-perfil" data-targetid="<?= $targetUserId ?>">
                            <span class="btn-text">+ Adicionar Amigo</span>
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Abas Lógicas Inferiores -->
        <nav class="profile-tabs">
            <button class="tab-btn active" data-target="tab-feelings">Feelings</button>
            <button class="tab-btn" data-target="tab-cla">Clã</button>
            <button class="tab-btn" data-target="tab-amigos">Amigos</button>
        </nav>
    </div>

    <!-- Corpos Condicionais das Abas -->
    <div class="profile-body">
        
        <!-- Tab 01: Feelings (Feed Cards Estilo X) -->
        <div id="tab-feelings" class="tab-content active">
            <!-- O Javascript "perfil.js" carregará magicamente as postagens do MVC API via Fetch aqui dentro! -->
            <div id="feedContainerWrapper">
                <!-- Loader Inicial UI -->
                <div class="glass-card feed-box">
                    <p class="empty-state">Atualizando feed e carregando seus pensamentos...</p>
                </div>
            </div>
        </div>

        <!-- Tab 02: Clãs c/ Tabela -->
        <div id="tab-cla" class="tab-content">
            <div class="glass-card full-box">
                <div class="pill-container">
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
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody id="clanTableBody">
                            <tr><td colspan="3" style="text-align: center; color: rgba(255,255,255,0.5);">Carregando Dados Místicos...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab 03: Lista de Amigos -->
        <div id="tab-amigos" class="tab-content">
            <div id="myFriendsListContainer">
                <div class="glass-card full-box" style="text-align: center;">
                    <p class="empty-state">Carregando conexões estelares do servidor...</p>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ============================================== -->
<!-- MODAIS SOBREPOSTOS DO SISTEMA UI -->
<!-- ============================================== -->

<!-- 0. Modal: NOTIFICAÇÕES (Inbox Convites) -->
<div class="modal-overlay d-none" id="modalNotifications">
    <div class="modal-box glass-card" style="max-height: 80vh; overflow-y: auto;">
        <div class="modal-header">
            <h3>🔔 Suas Notificações Mágicas</h3>
            <button class="btn-close" id="btnCloseNotifications">&times;</button>
        </div>
        <div id="notificationsListContent">
            <!-- Javascript carrega e cria listagem de Pedidos aqui -->
            <div class="text-center text-muted" style="padding: 2rem;">
                <p>Sincronizando Inbox com o Servidor...</p>
            </div>
        </div>
    </div>
</div>

<!-- 1. Modal Overlay Global: Adicionar Amigo -->
<div class="modal-overlay d-none" id="modalAddFriend">
    <div class="modal-box glass-card">
        <div class="modal-header">
            <h3>Buscar novos Amigos e Convites</h3>
            <button class="btn-close" id="btnCloseModalAmigo" title="Fechar janela ignorando">&times;</button>
        </div>
        <div class="modal-body">
            <input type="text" id="searchFriendInput" class="search-input" placeholder="Pesquise por e-mail ou nome real...">
            <div class="search-results" id="searchResults">
                <p class="empty-state text-center mt-3" style="font-size: 0.95rem;">Busque pelas pessoas na base conectada e lhes envie um convite imediato pro feel.it!</p>
            </div>
        </div>
    </div>
</div>

<!-- 2. Modal Overlay: Criação do Feeling com Opções Radio de Privacidade! -->
<div class="modal-overlay d-none" id="modalNovoFeeling">
    <div class="modal-box glass-card" style="max-width: 650px;">
        <div class="modal-header">
            <h3>Expresse um novo Feeling!</h3>
            <button class="btn-close" id="btnCloseFeeling" title="Fechar painel de Publicação">&times;</button>
        </div>
        <div class="modal-body">
            
            <!-- Área de Texto Rico com Limitador JavaScript -->
            <textarea id="feelingTextInput" class="feeling-textarea" maxlength="5000" rows="5" placeholder="O que você está sentindo/pensando agora com grande fervor?"></textarea>
            <div class="char-counter">
                <span id="charCount">0</span> / 5000
            </div>

            <!-- Caixas Segregadas em Selectores de Privacidade JS Ocultas -->
            <div class="privacy-settings mt-3">
                <p class="privacy-title">Onde e para quem você quer compartilhar o momento?</p>
                <div class="radio-group-main">
                    <label class="radio-option">
                        <input type="radio" name="publish_target" value="cla" id="radioTargetCla"> Postar Exclusivo em Clã
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="publish_target" value="todos" id="radioTargetTodos" checked> Apenas postar para todos
                    </label>
                </div>

                <!-- Modulo Oculto 1: Select de Clã -->
                <div id="wrapperSelectCla" class="privacy-wrapper d-none">
                    <label for="selectClaOptions">Selecione o Clã de destino listado:</label>
                    <select id="selectClaOptions" class="select-input mt-1">
                        <option value="" disabled selected>Abra o leque e Escolha um Clã seu...</option>
                        <option value="1">O Círculo Escaldante</option>
                        <option value="2">Clube de Leitura Avançado Noturno</option>
                    </select>
                </div>

                <!-- Modulo Oculto 2: Radios Intercaladores Estilosos de Amigos ou Globais -->
                <div id="wrapperSelectTodos" class="privacy-wrapper">
                    <label>Selecione a amplitude de filtro base de quem vai ver isso:</label>
                    <div class="radio-group-sub mt-1">
                        <label class="radio-option">
                            <input type="radio" name="public_visibility" value="public" checked> Publicamente p/ Todos
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="public_visibility" value="friends_only"> Fechado Apenas Amigos
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="public_visibility" value="private"> 🔒 Diário Oculto (Só eu)
                        </label>
                    </div>
                </div>
            </div>

            <!-- Master CTA Dinâmico Envio Back-end API Call POST -->
            <div class="modal-footer" style="margin-top: 2rem;">
                <button type="button" class="btn-primary" id="btnSubmitFeeling" style="width: 100%;">Publicar Novo Feeling</button>
            </div>

        </div>
    </div>
</div>

<!-- 3. Modal Criar Clã (Exclusivo do Perfil UI Refinada) -->
<div id="modalCreateClan" class="glass-modal d-none" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(10px);">
    <div class="glass-card" style="width: 100%; max-width: 550px; padding: 40px; border-radius: 24px; background: linear-gradient(145deg, rgba(30, 41, 59, 0.8), rgba(15, 23, 42, 0.9)); border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 20px 50px rgba(0,0,0,0.6);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 15px;">
            <h2 style="color: white; margin: 0; font-weight: 300; display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 1.8rem;">🏰</span> Fundar Novo Clã
            </h2>
            <button id="btnCloseClanModal" style="background: rgba(255,255,255,0.05); border: border: 1px solid rgba(255,255,255,0.1); border-radius: 50%; width: 35px; height: 35px; color: white; cursor: pointer; text-align: center; transition: all 0.2s ease;">&times;</button>
        </div>
        
        <form id="formCreateClan" enctype="multipart/form-data">
            <div style="margin-bottom: 20px;">
                <label style="color: var(--neon-main); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: block;">Nome Oficial do Clã</label>
                <input type="text" name="name" required placeholder="Ex: Cavaleiros de Prata..." style="width: 100%; padding: 15px; border-radius: 12px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); color: white; font-size: 1rem; outline:none; transition: border-color 0.3s ease;">
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="color: rgba(255,255,255,0.6); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: block;">Estatuto / Descrição</label>
                <textarea name="description" rows="3" placeholder="Quais as regras e o objetivo desta comunidade?" style="width: 100%; padding: 15px; border-radius: 12px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); color: white; font-size: 0.95rem; resize: none; outline:none;"></textarea>
            </div>
            
            <div style="margin-bottom: 30px; display: flex; gap: 20px;">
                <div style="flex: 1;">
                    <label style="color: rgba(255,255,255,0.6); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: block;">Visibilidade</label>
                    <select name="visibility" style="width: 100%; padding: 14px; border-radius: 12px; background: #0f172a; border: 1px solid rgba(255,255,255,0.1); color: white; font-size: 0.95rem; outline:none;">
                        <option value="public">🌍 Público (Aberto)</option>
                        <option value="private">🔒 Privado (Convite)</option>
                    </select>
                </div>
                <div style="flex: 1;">
                    <label style="color: rgba(255,255,255,0.6); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: block;">Capa Oficial (Opcional)</label>
                    <div style="background: rgba(0,0,0,0.3); border: 1px dashed rgba(255,255,255,0.2); padding: 10px; border-radius: 12px; text-align: center;">
                        <input type="file" name="clan_pic" accept="image/*" style="width: 100%; color: white; font-size: 0.8rem;">
                    </div>
                </div>
            </div>
            
            <button type="submit" class="comment-submit-btn" style="width: 100%; padding: 15px; font-size: 1.1rem; letter-spacing: 0.5px; border-radius: 12px; background: linear-gradient(90deg, var(--neon-main), #6366f1); box-shadow: 0 5px 20px rgba(14, 165, 233, 0.4);">
                ⚔️ Declarar Fundação do Clã
            </button>
        </form>
    </div>
</div>

<?php include 'header/footer.php'; ?>
