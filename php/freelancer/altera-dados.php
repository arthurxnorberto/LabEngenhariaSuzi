<?php
header('Content-type: application/json; charset=utf-8');

use \Firebase\JWT\JWT;
require_once("../vendor/autoload.php");
require_once("../config.php");
include("../recebe-jwt.php");

$input = @json_decode(file_get_contents("php://input"));

if($input == null or !isset($input->nome) or !isset($input->email) or !isset($input->cpfcnpj) 
        or !isset($input->senha) or !isset($input->especialidade)) {
    echo json_encode(['resultado' => false, 'mensagem' => "Requisição invalida"]);
    exit;
}

$id = $token->data->id;

function esp($esp, $id, $dsn, $user, $password){
    
    try {
        $pdo = new PDO($dsn, $user, $password);
        $sql = 'DELETE FROM freelancer_especialidade WHERE id_freelancer = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        foreach($esp as $num){
            $sql = 'INSERT INTO freelancer_especialidade(id_freelancer, id_especialidade) VALUES (:id_free, :id_esp)';
            $stmt = $pdo->prepare($sql);
            $stmt -> bindParam(':id_free', $id, PDO::PARAM_INT);
            $stmt ->bindParam(':id_esp', $num, PDO::PARAM_INT);
            $stmt -> execute();
        }
        $pdo = null;
    } catch (PDOException $e) {
        //TODO: tratar exeção
    }
}

try{
    $pdo = new PDO($dsn, $user, $password);
    if($debug) {
        //permite que mensagens de erro sejam mostradas
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    }
    $query = 'UPDATE freelancer SET nome = :nome, email = :email, cpf = :cpf, senha = :senha WHERE id_freelancer = :id_freelancer';
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome',$input->nome, PDO::PARAM_STR);
    $stmt->bindParam(':email', $input->email, PDO::PARAM_STR);
    $stmt->bindParam(':cpf', $input->cpfcnpj, PDO::PARAM_STR);
    $stmt->bindParam(':senha', $input->senha, PDO::PARAM_STR);
    $stmt->bindParam(':id_freelancer', $id, PDO::PARAM_INT, PDO::PARAM_STR);
    
    $result = $stmt->execute();
    
    esp($esp);
    
    if(!$result){
        //TODO: Enviar a mensagem de erro retornada pelo PDO
        echo json_encode(['resultado' => false, 'mensagem' => 'Erro no Banco de Dados']);
        exit;
    } else {
        echo json_encode(['resultado' => true]);
    }
    
} catch (PDOException $e) {
        //TODO: Enviar a mensagem de erro retornada pelo PDO
        echo json_encode(['resultado' => false, 'mensagem' => "Não foi possivel realizar o Cadastro"]);
}

$pdo = null;

exit;