<?php
session_start();
require_once 'config.php';
require_once 'db_config.php';

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Configurações SMTP
$email_remetente = $smtp_from_email;
$senha_email = $smtp_pass;

// Dados recebidos
$account_id = $_SESSION['account_id'];
$email_sessao = $_SESSION['email'];

$acao = $_POST['acao'] ?? 'salvar';
$id_convite = $_POST['id_convite'] ?? null;
$nome = $_POST['nome'] ?? '';
$email = $_POST['email'] ?? '';
$tipo_usuario = $_POST['tipo_usuario'] ?? 'Motorista';

// Permissões
$pode_gerenciar_viagens = isset($_POST['pode_gerenciar_viagens']) ? 1 : 0;
$pode_gerenciar_abastecimentos = isset($_POST['pode_gerenciar_abastecimentos']) ? 1 : 0;
$pode_gerenciar_manutencoes = isset($_POST['pode_gerenciar_manutencoes']) ? 1 : 0;
$pode_gerenciar_clientes = isset($_POST['pode_gerenciar_clientes']) ? 1 : 0;
$pode_gerenciar_usuarios = isset($_POST['pode_gerenciar_usuarios']) ? 1 : 0;
$pode_ver_relatorios = isset($_POST['pode_ver_relatorios']) ? 1 : 0;

$token = bin2hex(random_bytes(16));

try {
    // 🔥 REMOÇÃO
    if ($acao === 'remover') {
        if (!$id_convite) die('ID não informado.');

        $stmt = $pdo->prepare("SELECT * FROM convites_usuarios WHERE id = :id AND account_id = :account_id");
        $stmt->execute(['id' => $id_convite, 'account_id' => $account_id]);
        $convite = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$convite) die('Convite não encontrado.');

        if ($convite['email'] === $email_sessao) die('Não é possível remover o usuário Master.');

        if ($convite['status'] === 'pendente') {
            $pdo->prepare("DELETE FROM convites_usuarios WHERE id = :id AND account_id = :account_id")
                ->execute(['id' => $id_convite, 'account_id' => $account_id]);
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND account_id = :account_id");
            $stmt->execute(['email' => $convite['email'], 'account_id' => $account_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $pdo->prepare("DELETE FROM permissoes_usuarios WHERE user_id = :user_id")
                    ->execute(['user_id' => $user['id']]);

                $pdo->prepare("DELETE FROM users WHERE id = :user_id AND account_id = :account_id")
                    ->execute(['user_id' => $user['id'], 'account_id' => $account_id]);
            }

            $pdo->prepare("DELETE FROM convites_usuarios WHERE id = :id AND account_id = :account_id")
                ->execute(['id' => $id_convite, 'account_id' => $account_id]);
        }

        header('Location: usuarios.php');
        exit;
    }

    // 🔥 EDIÇÃO
    if ($id_convite) {
        $stmt = $pdo->prepare("SELECT * FROM convites_usuarios WHERE id = :id AND account_id = :account_id");
        $stmt->execute(['id' => $id_convite, 'account_id' => $account_id]);
        $convite = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$convite) die('Convite não encontrado.');

        $pdo->prepare("UPDATE convites_usuarios SET 
            nome = :nome, tipo_usuario = :tipo_usuario,
            pode_gerenciar_viagens = :viagens,
            pode_gerenciar_abastecimentos = :abastecimentos,
            pode_gerenciar_manutencoes = :manutencoes,
            pode_gerenciar_clientes = :clientes,
            pode_gerenciar_usuarios = :usuarios,
            pode_ver_relatorios = :relatorios
            WHERE id = :id AND account_id = :account_id")
            ->execute([
                'nome' => $nome,
                'tipo_usuario' => $tipo_usuario,
                'viagens' => $pode_gerenciar_viagens,
                'abastecimentos' => $pode_gerenciar_abastecimentos,
                'manutencoes' => $pode_gerenciar_manutencoes,
                'clientes' => $pode_gerenciar_clientes,
                'usuarios' => $pode_gerenciar_usuarios,
                'relatorios' => $pode_ver_relatorios,
                'id' => $id_convite,
                'account_id' => $account_id
            ]);

        if ($convite['status'] === 'ativo') {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND account_id = :account_id");
            $stmt->execute(['email' => $email, 'account_id' => $account_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $pdo->prepare("UPDATE permissoes_usuarios SET 
                    pode_gerenciar_viagens = :viagens,
                    pode_gerenciar_abastecimentos = :abastecimentos,
                    pode_gerenciar_manutencoes = :manutencoes,
                    pode_gerenciar_clientes = :clientes,
                    pode_gerenciar_usuarios = :usuarios,
                    pode_ver_relatorios = :relatorios
                    WHERE user_id = :user_id")
                    ->execute([
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

        header('Location: usuarios.php');
        exit;
    }

    // 🔥 CADASTRO NOVO
    $stmt = $pdo->prepare("INSERT INTO convites_usuarios 
        (account_id, nome, email, tipo_usuario, token, 
        pode_gerenciar_viagens, pode_gerenciar_abastecimentos, 
        pode_gerenciar_manutencoes, pode_gerenciar_clientes, 
        pode_gerenciar_usuarios, pode_ver_relatorios, status) 
        VALUES 
        (:account_id, :nome, :email, :tipo_usuario, :token, 
        :viagens, :abastecimentos, :manutencoes, :clientes, 
        :usuarios, :relatorios, 'pendente')");

    $stmt->execute([
        'account_id' => $account_id,
        'nome' => $nome,
        'email' => $email,
        'tipo_usuario' => $tipo_usuario,
        'token' => $token,
        'viagens' => $pode_gerenciar_viagens,
        'abastecimentos' => $pode_gerenciar_abastecimentos,
        'manutencoes' => $pode_gerenciar_manutencoes,
        'clientes' => $pode_gerenciar_clientes,
        'usuarios' => $pode_gerenciar_usuarios,
        'relatorios' => $pode_ver_relatorios
    ]);

    // 🔥 Enviar e-mail de convite
    $link = "https://gestortruck.com.br/cadastro_usuario.php?token=" . $token;

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = $smtp_host;
    $mail->SMTPAuth   = true;
    $mail->Username   = $email_remetente;
    $mail->Password   = $senha_email;
    $mail->SMTPSecure = $smtp_secure;
    $mail->Port       = $smtp_port;

    $mail->setFrom($email_remetente, $smtp_from_name);
    $mail->addAddress($email, $nome);

    $mail->isHTML(true);
    $mail->Subject = 'Convite para acessar o Gestor Truck';
    $mail->Body = "
        <h2>Olá, $nome</h2>
        <p>Você foi convidado para acessar o sistema <strong>Gestor Truck</strong>.</p>
        <p>Clique no botão abaixo para finalizar seu cadastro:</p>
        <p>
            <a href='$link' style='padding:10px 20px; background-color:#2563eb; color:white; text-decoration:none; border-radius:5px;'>
                Finalizar Cadastro
            </a>
        </p>
        <p>Ou acesse diretamente:<br><a href='$link'>$link</a></p>
        <br>
        <p>Atenciosamente,<br>Equipe Gestor Truck</p>
    ";

    $mail->send();

$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'usuarios.php';
    header('Location: ' . $redirect . '?status=sucesso');
    exit;

} catch (Exception $e) {
    die("Erro: " . $e->getMessage());
}
?>
