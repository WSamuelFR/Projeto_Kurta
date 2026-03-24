<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/model/ClanModel.php';

$clanId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($clanId === 0) {
    echo "Clã inválido."; exit;
}

$db = (new Database())->getConnection();
$clanModel = new ClanModel($db);

$clanData = $clanModel->getClanInfo($clanId);
if (!$clanData) {
    echo "Clã não encontrado no sistema."; exit;
}

// Security: User Role
$userRole = $clanModel->getUserRole($clanId, $_SESSION['user_id']); // "rei", "lider", "aldeao", ou null
$isMember = ($userRole !== null);

// If clan is private and user is not member, theoretically we'd hide posts. 
// For now, let's just show basic UI.

$pageTitle = "Clã: " . htmlspecialchars($clanData['name_clan']);
$extraScript = "../assets/js/clan.js";

$clanPic = $clanData['clan_pic'];
if (strpos($clanPic, 'http') === false) {
    $clanPic = '../' . $clanPic;
}

include 'header/header.php';
?>

<!-- Pass Clan Data to Javascript scope securely -->
<script>
    window.CURRENT_CLAN_ID = <?php echo $clanId; ?>;
    window.USER_ROLE = "<?php echo $userRole ? $userRole : 'visitante'; ?>";
    window.CURRENT_USER_ID = "<?php echo $_SESSION['user_id']; ?>";
</script>

<div class="profile-container" style="max-width: 900px; margin: 0 auto; padding-top: 2rem;">
    <!-- Clan Header Glassmorphism -->
    <div class="glass-card" style="position: relative; overflow: hidden; margin-bottom: 2rem;">
        <div style="height: 120px; background: linear-gradient(135deg, rgba(14,165,233,0.3) 0%, rgba(0,0,0,0.8) 100%);"></div>
        <div style="padding: 20px; position: relative;">
            <img src="<?php echo htmlspecialchars($clanPic); ?>" style="width: 100px; height: 100px; border-radius: 20px; border: 4px solid var(--bg-dark); position: absolute; top: -50px; left: 20px; object-fit: cover; box-shadow: 0 5px 15px rgba(0,0,0,0.5);">
            
            <div style="margin-left: 130px; margin-top: -10px;">
                <h1 style="color: white; margin: 0; font-size: 1.8rem; font-weight: 500; display: flex; align-items: center; gap: 10px;">
                    <?php echo htmlspecialchars($clanData['name_clan']); ?>
                    <?php if($clanData['visibility'] == 'private'): ?>
                        <span style="font-size: 0.9rem; background: rgba(255,255,255,0.1); padding: 3px 8px; border-radius: 10px;">🔒 Privado</span>
                    <?php else: ?>
                        <span style="font-size: 0.9rem; background: rgba(14,165,233,0.1); padding: 3px 8px; border-radius: 10px; color: var(--neon-main);">🌍 Público</span>
                    <?php endif; ?>
                </h1>
                <p style="color: var(--text-light); margin-top: 10px; font-size: 0.95rem;">
                    <?php echo nl2br(htmlspecialchars($clanData['description'])); ?>
                </p>
                
                <div style="margin-top: 20px; display: flex; flex-wrap: wrap; align-items: center; gap: 15px;">
                    <?php if($isMember): ?>
                        <div style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 10px 18px; border-radius: 12px; font-size: 0.85rem; color: white; display: flex; align-items: center; gap: 8px;">
                            <span>Patente:</span> <strong style="color: var(--neon-main); text-transform: capitalize; font-size: 1rem;"><?php echo $userRole; ?></strong>
                        </div>
                    <?php else: ?>
                        <button id="btnJoinClan" class="comment-submit-btn" style="padding: 10px 25px; font-weight: bold;">Entrar p/ Corte</button>
                    <?php endif; ?>

                    <!-- Botão Membros (Aberto a todos para ver) -->
                    <button id="btnMembrosClan" style="background: rgba(14, 165, 233, 0.15); border: 1px solid rgba(14, 165, 233, 0.4); color: white; border-radius: 12px; padding: 10px 20px; font-weight: 500; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 8px;">
                        👥 Lista de Membros
                    </button>

                    <!-- Botão Gerenciar Clã (Apenas Rei) -->
                    <?php if($userRole === 'rei'): ?>
                        <button id="btnSettingsClan" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: white; border-radius: 12px; padding: 10px 20px; font-weight: 500; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 8px;">
                            ⚙️ Gerência Real
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Clan Action/Feed Integrado Full Width -->
    <div style="display: block; width: 100%;">
        <div id="clanFeedColumn" style="width: 100%; max-width: 700px; margin: 0 auto;">
            <?php if($isMember || $clanData['visibility'] == 'public'): ?>
                
                <h3 style="color:var(--text-main); margin-bottom: 1.5rem; font-weight: 300; text-align: center;">📜 Mural do Clã</h3>
                
                <?php if($userRole === 'rei' || $userRole === 'lider'): ?>
                <!-- Botão de Criação de Post (Apenas Rei e Lider) -->
                <div style="text-align: center; margin-bottom: 25px;">
                     <a href="#" id="btnMasterClanFeeling" class="btn-primary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 500; font-size: 0.95rem; text-decoration: none; padding: 10px 20px; border-radius: 12px;">
                        <span style="font-size: 1.2rem;">📝</span>
                        <span>novo feeling!</span>
                    </a>
                </div>
                <?php endif; ?>

                <div id="feedContainerWrapper">
                    <div class="glass-card feed-box" style="text-align: center; padding: 40px;">
                        <span style="font-size: 2rem; color: rgba(255,255,255,0.5);">🌪️</span>
                        <p class="empty-state" style="margin-top: 15px;">Os ventos estão calmos... Ninguém postou neste clã ainda.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="glass-card feed-box" style="text-align: center; padding: 40px;">
                    <span style="font-size: 2rem; color: rgba(255,255,255,0.5);">🚫</span>
                    <p class="empty-state" style="margin-top: 15px; color: #ef4444;">Este clã é de domínio privado. Junte-se para ver os segredos.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ================= MODAIS DO CLÃ ================= -->

<!-- Modal: Lista de Membros -->
<div id="modalClanMembers" class="glass-modal d-none" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.75); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(8px);">
    <div class="glass-card" style="width: 100%; max-width: 800px; height: 75vh; display: flex; flex-direction: column; padding: 40px; border-radius: 20px; background: linear-gradient(145deg, rgba(20, 30, 45, 0.95), rgba(10, 15, 25, 0.95)); shadow: 0 10px 40px rgba(0,0,0,0.8);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 15px;">
            <h3 style="color: white; margin: 0; font-weight: 300; font-size: 1.6rem;">👥 Participantes do Clã</h3>
            <button class="btnCloseModalClan" style="background: transparent; border: none; font-size: 2rem; color: rgba(255,255,255,0.5); cursor: pointer; transition: color 0.3s; line-height: 1;">&times;</button>
        </div>
        <div id="membersListAjax" style="flex: 1; overflow-y: auto; padding-right: 15px;">
            <p style="text-align: center; color: rgba(255,255,255,0.5); font-size: 1.1rem; margin-top: 50px;">Carregando guerreiros...</p>
        </div>
    </div>
</div>

<!-- Modal: Gerenciar (Rei Apenas) -->
<?php if($userRole === 'rei'): ?>
<div id="modalClanSettings" class="glass-modal d-none" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(10px);">
    <div class="glass-card" style="width: 100%; max-width: 500px; padding: 35px; border-radius: 20px; border: 1px solid rgba(239, 68, 68, 0.3); background: rgba(15, 10, 10, 0.95);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 15px;">
            <h3 style="color: #ef4444; margin: 0; font-weight: 400; font-size: 1.5rem;">👑 Gerência Real do Clã</h3>
            <button class="btnCloseModalClan" style="background: transparent; border: none; font-size: 1.8rem; color: rgba(255,255,255,0.5); cursor: pointer;">&times;</button>
        </div>
        
        <form id="formClanSettings">
            <div style="margin-bottom: 15px;">
                <label style="color: var(--text-light); font-size: 0.85rem;">Nome do Clã</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($clanData['name_clan']); ?>" style="width: 100%; padding: 12px; border-radius: 10px; background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.1); color: white; margin-top: 5px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="color: var(--text-light); font-size: 0.85rem;">Descrição</label>
                <textarea name="description" rows="3" style="width: 100%; padding: 12px; border-radius: 10px; background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.1); color: white; resize: none; margin-top: 5px;"><?php echo htmlspecialchars($clanData['description']); ?></textarea>
            </div>
            <div style="margin-bottom: 25px;">
                <label style="color: var(--text-light); font-size: 0.85rem;">Visibilidade (Aberto ou Convite)</label>
                <select name="visibility" style="width: 100%; padding: 12px; border-radius: 10px; background: #000; border: 1px solid rgba(255,255,255,0.1); color: white; margin-top: 5px;">
                    <option value="public" <?php if($clanData['visibility']=='public') echo 'selected';?>>🌍 Público</option>
                    <option value="private" <?php if($clanData['visibility']=='private') echo 'selected';?>>🔒 Privado</option>
                </select>
            </div>
            
            <button type="submit" class="comment-submit-btn" style="width: 100%; padding: 12px;">💾 Salvar Alterações</button>
        </form>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px dashed rgba(239, 68, 68, 0.4); text-align: center;">
            <p style="color: rgba(255,255,255,0.5); font-size: 0.8rem; margin-bottom: 10px;">Ação Irreversível de Realeza</p>
            <button id="btnDeleteClan" style="background: transparent; border: 1px solid rgba(239, 68, 68, 0.6); color: #ef4444; padding: 10px 20px; border-radius: 10px; cursor: pointer; transition: all 0.2s;">
                ☠️ Exterminar Clã
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal: Ditar Comunicado (Rei/Líder) -->
<div id="modalClanFeeling" class="glass-modal d-none" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(10px);">
    <div class="glass-card" style="width: 100%; max-width: 550px; padding: 30px; border-radius: 20px; background: rgba(15, 20, 30, 0.95); border: 1px solid rgba(14,165,233,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 15px;">
            <h3 style="color: var(--neon-main); margin: 0; font-weight: 300; font-size: 1.5rem;">Expresse um novo Feeling!</h3>
            <button id="btnCloseClanFeeling" style="background: transparent; border: none; font-size: 1.8rem; color: rgba(255,255,255,0.5); cursor: pointer;">&times;</button>
        </div>
        
        <div style="margin-bottom: 20px;">
            <textarea id="clanFeelingTextInput" rows="5" maxlength="3000" placeholder="No que você está pensando? (Máx 3000 chars)" style="width: 100%; padding: 15px; border-radius: 15px; background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); color: white; resize: none; font-size: 1.05rem;"></textarea>
            <div style="text-align: right; margin-top: 5px; font-size: 0.8rem; color: var(--text-muted);"><span id="clanCharCount">0</span> / 3000</div>
        </div>
        
        <button id="btnSubmitClanFeeling" class="comment-submit-btn" style="width: 100%; padding: 15px; font-size: 1.1rem; font-weight: bold;">Publicar Feeling</button>
    </div>
</div>

<?php include 'header/footer.php'; ?>
<!-- Imports Globais Auxiliares -->
<script src="../assets/js/comments.js"></script>
