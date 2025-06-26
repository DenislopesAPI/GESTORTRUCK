<?php
session_start();
require_once 'db_config.php';

$account_id = $_SESSION['account_id'];
$nome = $_POST['nome'];
$email = $_POST['email'];
$id_convite = $_POST['id_convite'] ?? null;

$pode_gerenciar_viagens = isset($_POST['pode_gerenciar_viagens']) ? 1 : 0;
$pode_gerenciar_abastecimentos = isset($_POST['pode_gerenciar_abastecimentos']) ? 1 : 0;
$pode_gerenciar_manutencoes = isset($_POST['pode_gerenciar_manutencoes']) ? 1 : 0;
$pode_gerenciar_clientes = isset($_POST['pode_gerenciar_clientes']) ? 1 : 0;
$pode_gerenciar_usuarios = isset($_POST['pode_gerenciar_usuarios']) ? 1 : 0;
$pode_ver_relatorios = isset($_POST['pode_ver_relatorios']) ? 1 : 0;

$token = bin2hex(random_bytes(16));

try {
    // Verificar se é edição ou novo cadastro
    if ($id_convite) {
        // Buscar dados do convite
        $stmt = $pdo->prepare("SELECT * FROM convites_usuarios WHERE id = :id AND account_id = :account_id");
        $stmt->execute([
            'id' => $id_convite,
            'account_id' => $account_id
        ]);
        $convite = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$convite) {
            die('Convite não encontrado ou não pertence à sua conta.');
        }

        // Atualizar convites_usuarios
        $stmt = $pdo->prepare("UPDATE convites_usuarios SET 
            nome = :nome, 
            pode_gerenciar_viagens = :viagens,
            pode_gerenciar_abastecimentos = :abastecimentos,
            pode_gerenciar_manutencoes = :manutencoes,
            pode_gerenciar_clientes = :clientes,
            pode_gerenciar_usuarios = :usuarios,
            pode_ver_relatorios = :relatorios
            WHERE id = :id AND account_id = :account_id");

        $stmt->execute([
            'nome' => $nome,
            'viagens' => $pode_gerenciar_viagens,
            'abastecimentos' => $pode_gerenciar_abastecimentos,
            'manutencoes' => $pode_gerenciar_manutencoes,
            'clientes' => $pode_gerenciar_clientes,
            'usuarios' => $pode_gerenciar_usuarios,
            'relatorios' => $pode_ver_relatorios,
            'id' => $id_convite,
            'account_id' => $account_id
        ]);

        // Se o usuário já está ativo, atualizar permissões dele
        if ($convite['status'] === 'ativo') {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND account_id = :account_id");
            $stmt->execute([
                'email' => $email,
                'account_id' => $account_id
            ]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $stmt = $pdo->prepare("UPDATE permissoes_usuarios SET 
                    pode_gerenciar_viagens = :viagens,
                    pode_gerenciar_abastecimentos = :abastecimentos,
                    pode_gerenciar_manutencoes = :manutencoes,
                    pode_gerenciar_clientes = :clientes,
                    pode_gerenciar_usuarios = :usuarios,
                    pode_ver_relatorios = :relatorios
                    WHERE user_id = :user_id");

                $stmt->execute([
                    'viagens' => $pode_gerenciar_viagens,
                    'abastecimentos' => $pode_gerenciar_abastecimentos,
                    'manutencoes' => $pode_gerenciar_manutencoes,
                    'clientes' => $pode_gerenciar_clientes,
                    'usuarios' => $pode_gerenciar_usuarios,
                    'relatorios' => $pode_ver_relatorios,
                    'user_id' => $user['id']
                ]);
            }
        }

    } else {
        // Novo cadastro de convite
        $stmt = $pdo->prepare("INSERT INTO convites_usuarios 
        (account_id, nome, email, token, 
        pode_gerenciar_viagens, pode_gerenciar_abastecimentos, 
        pode_gerenciar_manutencoes, pode_gerenciar_clientes, 
        pode_gerenciar_usuarios, pode_ver_relatorios, status) 
        VALUES 
        (:account_id, :nome, :email, :token, 
        :viagens, :abastecimentos, :manutencoes, :clientes, 
        :usuarios, :relatorios, 'pendente')");

        $stmt->execute([
            'account_id' => $account_id,
            'nome' => $nome,
            'email' => $email,
            'token' => $token,
            'viagens' => $pode_gerenciar_viagens,
            'abastecimentos' => $pode_gerenciar_abastecimentos,
            'manutencoes' => $pode_gerenciar_manutencoes,
            'clientes' => $pode_gerenciar_clientes,
            'usuarios' => $pode_gerenciar_usuarios,
            'relatorios' => $pode_ver_relatorios
        ]);

        // Enviar e-mail de convite
        require 'enviar_email_convite.php';
        enviarEmailConvite($email, $nome, $token);
    }

    header('Location: usuarios.php');
    exit;
} catch (Exception $e) {
    die('Erro: ' . $e->getMessage());
}
?>
