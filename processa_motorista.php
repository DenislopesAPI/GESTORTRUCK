<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$account_id = $_SESSION['account_id'];

$nome          = $_POST['nome']          ?? '';
$cpf           = $_POST['cpf']           ?? '';
$telefone      = $_POST['telefone']      ?? '';
$endereco      = $_POST['endereco']      ?? null;
$cnh           = $_POST['cnh']           ?? '';
$categoria_cnh = $_POST['categoria_cnh'] ?? '';
$validade_cnh  = $_POST['validade_cnh']  ?? '';
$observacao    = $_POST['observacao']    ?? null;

if (!$nome || !$cpf || !$telefone || !$cnh || !$categoria_cnh || !$validade_cnh) {
    header('Location: motoristas.php');
    exit;
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO motoristas (
            account_id, nome, telefone, cpf, endereco, numero_cnh,
            validade_cnh, categoria_cnh, observacao, status
        ) VALUES (
            :account_id, :nome, :telefone, :cpf, :endereco, :numero_cnh,
            :validade_cnh, :categoria_cnh, :observacao, 'ativo'
        )"
    );

    $stmt->execute([
        'account_id'   => $account_id,
        'nome'         => $nome,
        'telefone'     => $telefone,
        'cpf'          => $cpf,
        'endereco'     => $endereco,
        'numero_cnh'   => $cnh,
        'validade_cnh' => $validade_cnh,
        'categoria_cnh'=> $categoria_cnh,
        'observacao'   => $observacao
    ]);

    header('Location: motoristas.php?sucesso=1');
    exit;
} catch (Exception $e) {
    die('Erro: ' . $e->getMessage());
}
