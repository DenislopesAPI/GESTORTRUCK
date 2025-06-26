<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$account_id = $_SESSION['account_id'];
$nome = $_POST['nome'] ?? '';
$telefone = $_POST['telefone'] ?? '';

if (!$nome || !$telefone) {
    header('Location: motoristas.php');
    exit;
}

$token = bin2hex(random_bytes(16));

try {
    $stmt = $pdo->prepare("INSERT INTO convites_usuarios (account_id, nome, telefone, tipo_usuario, token, status) VALUES (:account_id, :nome, :telefone, 'Motorista', :token, 'pendente')");
    $stmt->execute([
        'account_id' => $account_id,
        'nome'       => $nome,
        'telefone'   => $telefone,
        'token'      => $token
    ]);

    header('Location: motoristas.php?link=' . $token);
    exit;
} catch (Exception $e) {
    die('Erro: ' . $e->getMessage());
}
?>
