<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

session_start();
if(!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../model/ClanModel.php';

$clanId = $_GET['clan_id'] ?? 0;
if (!$clanId) {
    echo json_encode(['error' => 'Clan ID Missing']); exit;
}

$db = (new Database())->getConnection();
$clanModel = new ClanModel($db);

$members = $clanModel->getMembers($clanId);

// Definir o cargo de quem está vendo a lista (O Visualizador = Viewer)
$viewerRole = null;
foreach ($members as $mem) {
    if ($mem['user_id'] == $_SESSION['user_id']) {
        $viewerRole = $mem['role'];
        break;
    }
}

// Format safely to Frontend UI HTML or just basic JSON
$html = '';
if (count($members) === 0) {
    $html = '<p style="color:white; text-align:center;">Nenhum membro encontrado.</p>';
} else {
    foreach ($members as $mem) {
        $name = htmlspecialchars($mem['first_name'] . ' ' . $mem['last_name']);
        $pic = htmlspecialchars($mem['profile_pic'] ? $mem['profile_pic'] : 'assets/files/default_avatar.png');
        if (strpos($pic, 'http') === false && strpos($pic, '../') === false) { $pic = '../' . $pic; }
        
        $roleColor = '#94a3b8'; // Aldeao
        if ($mem['role'] == 'rei') $roleColor = '#fbbf24'; // Gold
        if ($mem['role'] == 'lider') $roleColor = '#38bdf8'; // Sky Blue
        
        $html .= '<div style="display:flex; align-items:center; justify-content:space-between; padding: 12px; background:rgba(255,255,255,0.05); margin-bottom: 10px; border-radius:12px; border: 1px solid rgba(255,255,255,0.02); flex-wrap: wrap; gap:10px;">';
        $html .= '    <div style="display:flex; align-items:center; gap: 12px;">';
        $html .= '        <img src="'.$pic.'" onerror="this.src=\'https://ui-avatars.com/api/?background=4f46e5&color=fff&name='.urlencode($mem['first_name'] . ' ' . $mem['last_name']).'\'" style="width:45px; height:45px; border-radius:50%; object-fit:cover; border: 2px solid '.$roleColor.';">';
        $html .= '        <div>';
        $html .= '            <div style="color:white; font-weight:500; font-size:1.05rem;">'.$name.'</div>';
        $html .= '            <div style="color:'.$roleColor.'; font-size:0.75rem; text-transform:uppercase; font-weight:bold; letter-spacing:1px; margin-top:3px;">'.$mem['role'].'</div>';
        $html .= '        </div>';
        $html .= '    </div>';
        
        $html .= '<div style="display:flex; gap: 8px; align-items:center; flex-wrap:wrap;">';
        
        // ==== BOTÕES ESPECIAIS DO REI ====
        if ($viewerRole === 'rei' && $mem['user_id'] != $_SESSION['user_id']) {
            if ($mem['role'] === 'aldeao') {
                $html .= '<button class="btn-outline btn-sm acao-patente-cla" data-targetid="'.$mem['user_id'].'" data-cargo="lider" style="border-color:#38bdf8; color:#38bdf8; padding: 4px 8px; font-size:0.8rem;">Promover a Líder</button>';
            } else if ($mem['role'] === 'lider') {
                $html .= '<button class="btn-outline btn-sm acao-patente-cla" data-targetid="'.$mem['user_id'].'" data-cargo="aldeao" style="border-color:#94a3b8; color:#94a3b8; padding: 4px 8px; font-size:0.8rem;">Rebaixar Aldeão</button>';
            }
            // Expulsar (Deixaremos o frontend pronto p/ um proximo backend kick_member se precisar)
            $html .= '<button class="btn-outline btn-sm acao-expulsar-membro" data-targetid="'.$mem['user_id'].'" style="border-color:#ef4444; color:#ef4444; padding: 4px 8px; font-size:0.8rem;">Expulsar</button>';
        }

        // Exibir btn Add Amigo p/ todos, desde que NÃO seja si mesmo.
        if ($mem['user_id'] != $_SESSION['user_id']) {
            $html .= '    <button class="btnAmigoClan" data-targetid="'.$mem['user_id'].'" style="background: rgba(14,165,233,0.1); border: 1px solid rgba(14,165,233,0.4); color: #0ea5e9; padding: 6px 12px; border-radius:8px; font-size:0.8rem; cursor:pointer;">+ Amigo</button>';
        }
        $html .= '</div></div>';
    }
}

echo json_encode(['status' => 'success', 'html' => $html]);
?>
