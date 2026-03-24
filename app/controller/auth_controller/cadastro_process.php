<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../model/cadastroModel.php';
require_once __DIR__ . '/../config/criptography.php';
require_once __DIR__ . '/../config/email_verifier.php';

// Recebe os dados brutos via POST do JS (Fetch)
$data = json_decode(file_get_contents("php://input"));

if(
    !empty($data->first_name) &&
    !empty($data->email) &&
    !empty($data->password)
) {
    // Trata e valida o email rigorosamente
    $validEmail = EmailVerifier::validateAndSanitize($data->email);

    if (!$validEmail) {
        http_response_code(400); // Bad Request
        echo json_encode(array("success" => false, "message" => "Formato de E-mail inválido. Verifique e tente novamente."));
        exit;
    }

    $cadastroModel = new CadastroModel();

    // Impede cadastro de emails ja salvos
    if($cadastroModel->emailExists($validEmail)) {
        http_response_code(409); // Conflict
        echo json_encode(array("success" => false, "message" => "Atenção: Este e-mail já está em uso na base de dados."));
        exit;
    }

    // Permite campos opcionais
    $lastName = !empty($data->last_name) ? $data->last_name : null;
    $phone = !empty($data->phone) ? $data->phone : null;
    
    // Processa a Criptografia da Senha
    $hashedPassword = Criptography::hashPassword($data->password);

    // Executa Transactions SQL do Model -> Inserindo nas duas tabelas
    if($cadastroModel->registerUser($data->first_name, $lastName, $phone, $validEmail, $hashedPassword)) {
        http_response_code(201); // Created
        echo json_encode(array("success" => true, "message" => "Conta criada com sucesso! Redirecionando..."));
    } else {
        http_response_code(503); // Service Unavailable
        echo json_encode(array("success" => false, "message" => "Ocorreu um erro no servidor ao processar o cadastro."));
    }

} else {
    // Faltou campos obrigatorios
    http_response_code(400); 
    echo json_encode(array("success" => false, "message" => "O Nome, E-mail e Senha são obrigatórios."));
}
?>
