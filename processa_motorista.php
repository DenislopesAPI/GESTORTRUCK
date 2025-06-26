<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$account_id = $_SESSION['account_id'];

$acao = $_POST['acao'] ?? 'salvar';
$id_motorista = $_POST['id_motorista'] ?? null;
$nome = $_POST['nome'] ?? '';
$status = $_POST['status'] ?? 'ativo';

try {
    if ($acao === 'remover') {
        if (!$id_motorista) die('ID n\xc3\xa3o informado.');
        $stmt = $pdo->prepare("DELETE FROM motoristas WHERE id = :id AND account_id = :account_id");
        $stmt->execute(['id' => $id_motorista, 'account_id' => $account_id]);
        header('Location: motoristas.php');
        exit;
    }

    if ($id_motorista) {
        $stmt = $pdo->prepare("UPDATE motoristas SET nome = :nome, status = :status WHERE id = :id AND account_id = :account_id");
        $stmt->execute([
            'nome' => $nome,
            'status' => $status,
            'id' => $id_motorista,
            'account_id' => $account_id
        ]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO motoristas (account_id, nome, status) VALUES (:account_id, :nome, :status)");
        $stmt->execute([
            'account_id' => $account_id,
            'nome' => $nome,
            'status' => $status
        ]);
    }

    header('Location: motoristas.php?status=sucesso');
    exit;
} catch (Exception $e) {
    die('Erro: ' . $e->getMessage());
}
?>
