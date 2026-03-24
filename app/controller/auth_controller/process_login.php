<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../../model/loginModel.php';
require_once __DIR__ . '/../../config/criptography.php';

// Recebe a raw string num request POST do JS (Fetch)
$data = json_decode(file_get_contents("php://input"));

if(!empty($data->email) && !empty($data->password)) {
    $loginModel = new LoginModel();
    $user = $loginModel->getUserByEmail($data->email);
    
    if($user) {
        // Verifica hash da senha criptografada 
        if(Criptography::verifyPassword($data->password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['level_acess'] = $user['level_acess'];
            $_SESSION['profile_pic'] = $user['profile_pic'];
            
            http_response_code(200);
            echo json_encode(array(
                "success" => true,
                "message" => "Login realizado com sucesso.",
                "user" => array(
                    "name" => $user['first_name'],
                    "level" => $user['level_acess']
                )
            ));
        } else {
            http_response_code(401);
            echo json_encode(array("success" => false, "message" => "Senha incorreta."));
        }
    } else {
        http_response_code(404);
        echo json_encode(array("success" => false, "message" => "E-mail não encontrado."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("success" => false, "message" => "Dados incompletos fornecidos. Preencha todos os campos."));
}
?>
