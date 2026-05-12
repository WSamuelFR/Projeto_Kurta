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

$filter = $_GET['filter'] ?? 'meus-clas';
$userId = $_SESSION['user_id'];
$db = (new Database())->getConnection();

// meus-clas: sou rei
// participei: sou lider ou aldeao
// todos: lista global ordenada pela (quantidade_membros) e Data.
// A "quantidade de atividade" pode ser aproximada aqui pela data de criação ou por uma view extra se tivéssemos.
// A requisição diz "listar clã pela quantidade de atividade e membros!". Faremos `ORDER BY total_membros DESC, c.created_at DESC`

$html = '';

try {
    $stmt = null;
    if ($filter === 'meus-clas') {
        $sql = "SELECT c.*, 
                (SELECT COUNT(*) FROM clan_member cm2 WHERE cm2.clan_id = c.clan_id) as total_membros, 
                cm.role 
                FROM clan c 
                JOIN clan_member cm ON c.clan_id = cm.clan_id 
                WHERE cm.user_id = :u AND cm.role = 'rei'
                ORDER BY c.created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([':u' => $userId]);
        
    } else if ($filter === 'participei') {
        $sql = "SELECT c.*, 
                (SELECT COUNT(*) FROM clan_member cm2 WHERE cm2.clan_id = c.clan_id) as total_membros, 
                cm.role 
                FROM clan c 
                JOIN clan_member cm ON c.clan_id = cm.clan_id 
                WHERE cm.user_id = :u AND cm.role != 'rei'
                ORDER BY c.created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([':u' => $userId]);
        
    } else if ($filter === 'todos') {
        $sql = "SELECT c.*, 
                (SELECT COUNT(*) FROM clan_member cm2 WHERE cm2.clan_id = c.clan_id) as total_membros,
                (SELECT role FROM clan_member cm3 WHERE cm3.clan_id = c.clan_id AND cm3.user_id = :u LIMIT 1) as user_role
                FROM clan c
                ORDER BY total_membros DESC, c.created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([':u' => $userId]);
    }

    $clans = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($clans) === 0) {
        $html = '<tr><td colspan="3" style="text-align: center; color: rgba(255,255,255,0.5); padding: 20px;">Nenhum clã encontrado nesta dimensão.</td></tr>';
    } else {
        foreach ($clans as $c) {
            $name = htmlspecialchars($c['name_clan']);
            $members = $c['total_membros'];
            $id = $c['clan_id'];
            
            // Check button style depending on filter
            $buttonHtml = '';
            
            if ($filter === 'todos') {
                $role = isset($c['user_role']) ? $c['user_role'] : null;
                if ($role) {
                    $buttonHtml = '<a href="clan.php?id='.$id.'" class="btn-outline btn-sm">Acessar a Página</a>';
                } else {
                    if ($c['visibility'] === 'public') {
                        // Needs to route through join or direct to clan
                        $buttonHtml = '<a href="clan.php?id='.$id.'" style="color:#0ea5e9; border: 1px solid #0ea5e9; padding:5px 10px; border-radius:8px; text-decoration:none;">Entrar</a>';
                    } else {
                        $buttonHtml = '<button onclick="alert(\'Funcionalidade Pendente: Enviar Solicitação para clã privado\')" style="color:#fbbf24; border: 1px solid #fbbf24; background:transparent; padding:5px 10px; border-radius:8px; cursor:pointer;">Enviar Solicitação</button>';
                    }
                }
            } else {
                $roleBadge = '';
                if(isset($c['role'])) {
                    if($c['role']=='rei') $roleBadge = '👑 Rei';
                    if($c['role']=='lider') $roleBadge = '🛡️ Líder';
                    if($c['role']=='aldeao') $roleBadge = '👤 Aldeão';
                }
                
                $buttonHtml = '<div style="display:flex; gap:10px; align-items:center;">';
                if($roleBadge) $buttonHtml .= '<span style="font-size:0.75rem; color:var(--text-light);">'.$roleBadge.'&nbsp;</span>';
                $buttonHtml .= '<a href="clan.php?id='.$id.'" class="btn-outline btn-sm">Acessar a Página</a>';
                $buttonHtml .= '</div>';
            }
            
            $html .= '<tr>';
            $html .= '  <td><span class="cla-name">'.$name.'</span></td>';
            $html .= '  <td><span class="badge-members">👥 '.$members.' vivos</span></td>';
            $html .= '  <td>'.$buttonHtml.'</td>';
            $html .= '</tr>';
        }
    }
    
    echo json_encode(['status' => 'success', 'html' => $html]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
