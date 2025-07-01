<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$account_id = $_SESSION['account_id'];

// Coletar dados
$nome = $_POST['nome'] ?? '';
$cpf = $_POST['cpf'] ?? '';
$rg = $_POST['rg'] ?? null;
$data_nascimento = $_POST['data_nascimento'] ?? '';
$telefone = $_POST['telefone'] ?? '';
$telefone_emergencia = $_POST['telefone_emergencia'] ?? null;
$email = $_POST['email'] ?? '';
$cnh = $_POST['cnh'] ?? '';
$categoria_cnh = $_POST['categoria_cnh'] ?? '';
$validade_cnh = $_POST['validade_cnh'] ?? '';
$data_admissao = $_POST['data_admissao'] ?? '';
$endereco = $_POST['endereco'] ?? '';
$bairro = $_POST['bairro'] ?? '';
$cidade = $_POST['cidade'] ?? '';
$estado = $_POST['estado'] ?? '';
$cep = $_POST['cep'] ?? '';
$banco = $_POST['banco'] ?? null;
$agencia = $_POST['agencia'] ?? null;
$conta = $_POST['conta'] ?? null;
$pix = $_POST['pix'] ?? null;
$observacoes = $_POST['observacoes'] ?? null;
$senha = $_POST['senha'] ?? '';

if (!$nome || !$cpf || !$cnh || !$telefone || !$email || !$senha) {
    header('Location: motoristas.php');
    exit;
}

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

try {
    $pdo->beginTransaction();

    // criar usuario
    $stmt = $pdo->prepare("INSERT INTO users (account_id, nome, email, senha) VALUES (:account_id, :nome, :email, :senha)");
    $stmt->execute([
        'account_id' => $account_id,
        'nome' => $nome,
        'email' => $email,
        'senha' => $senhaHash
    ]);
    $user_id = $pdo->lastInsertId();

    // permissões padrão zeradas
    $stmt = $pdo->prepare("INSERT INTO permissoes_usuarios (user_id, pode_gerenciar_viagens, pode_gerenciar_abastecimentos, pode_gerenciar_manutencoes, pode_gerenciar_clientes, pode_gerenciar_usuarios, pode_ver_relatorios) VALUES (:user_id,0,0,0,0,0,0)");
    $stmt->execute(['user_id' => $user_id]);

    // inserir motorista
    $stmt = $pdo->prepare("INSERT INTO motoristas (account_id, user_id, nome, cpf, rg, data_nascimento, cnh, categoria_cnh, validade_cnh, telefone, telefone_emergencia, email, endereco, bairro, cidade, estado, cep, banco, agencia, conta, pix, status, observacoes, data_admissao) VALUES (:account_id, :user_id, :nome, :cpf, :rg, :data_nascimento, :cnh, :categoria_cnh, :validade_cnh, :telefone, :telefone_emergencia, :email, :endereco, :bairro, :cidade, :estado, :cep, :banco, :agencia, :conta, :pix, 'ativo', :observacoes, :data_admissao)");
    $stmt->execute([
        'account_id' => $account_id,
        'user_id' => $user_id,
        'nome' => $nome,
        'cpf' => $cpf,
        'rg' => $rg,
        'data_nascimento' => $data_nascimento,
        'cnh' => $cnh,
        'categoria_cnh' => $categoria_cnh,
        'validade_cnh' => $validade_cnh,
        'telefone' => $telefone,
        'telefone_emergencia' => $telefone_emergencia,
        'email' => $email,
        'endereco' => $endereco,
        'bairro' => $bairro,
        'cidade' => $cidade,
        'estado' => $estado,
        'cep' => $cep,
        'banco' => $banco,
        'agencia' => $agencia,
        'conta' => $conta,
        'pix' => $pix,
        'observacoes' => $observacoes,
        'data_admissao' => $data_admissao
    ]);

    $pdo->commit();

    header('Location: motoristas.php?sucesso=1');
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    die('Erro: ' . $e->getMessage());
}
