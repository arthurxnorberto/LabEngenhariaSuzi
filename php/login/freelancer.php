<?php
header('Content-type: application/json; charset=utf-8');

use \Firebase\JWT\JWT;
require_once("../vendor/autoload.php");
require_once("../config.php");

// converto o input em json; o "@" remove a mensagem de erro (caso existir)
$input = @json_decode(file_get_contents("php://input"));

if($input == null or !isset($input->login) or !isset($input->senha)) {
    echo json_encode(['resultado' => false, 'mensagem' => "Requisição invalida"]);
    exit;
}

try{
    $pdo = new PDO($dsn, $user, $password);
    $stmt = $pdo->prepare("SELECT * FROM freelancer WHERE email = :login AND senha = :senha");
    $stmt->bindParam(':login',$input->login);
    $stmt->bindParam(':senha',md5($input->senha));
    $stmt->execute();
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e){
    //TODO: receber mensagem de erro do PDO
    echo json_encode(['resultado' => false, 'mensagem' => 'Erro no Banco de Dados']);
    exit;
}

if($resultado) {
    $horaAtual = time();
    $token = [
        'iat'  => $horaAtual,                            // Issued at: time when the token was generated
        'jti'  => base64_encode(mcrypt_create_iv(32)),   // Json Token Id: an unique identifier for the token
        'iss'  => $nomeServidor,                         // Issuer
        'nbf'  => $horaAtual,                            // Not before
        'exp'  => $horaAtual + (60*60),                  // Expire
        'data' => null                                   // Data to be signed
    ];
    $id = $resultado['id_freelancer'];
    $usuario = (object) ['id' => $id, 'tipo' => 'freelancer'];
    $token['data'] = $usuario; //adiciona o login aos dados que seram assinados pelo jwt
    $jwt = JWT::encode($token, $JWTkey, 'HS256'); //assina os dados do usuario
    echo json_encode(['resultado' => true, 'jwt' => $jwt]); //envia a resposta json
    
} else { //não encontrou o usuario
    echo json_encode(['resultado' => false, 'mensagem' => 'Login ou Senha Invalido']);
}
