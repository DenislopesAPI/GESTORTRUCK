<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config.php';
require_once 'db_config.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $documento = $_POST['documento'];
    $tipo_pessoa = $_POST['tipo_pessoa'] ?? '';
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    $token = bin2hex(random_bytes(16));

    if ($senha !== $confirmar_senha) {
        $erro = "❌ As senhas não conferem. Verifique e tente novamente.";
    } else {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        try {
            // Criar conta
            $stmt = $pdo->prepare("INSERT INTO accounts (nome_empresa, telefone, email, documento, created_at) 
            VALUES (:nome_empresa, :telefone, :email, :documento, NOW())");
            $stmt->execute([
                'nome_empresa' => $nome,
                'telefone' => $telefone,
                'email' => $email,
                'documento' => $documento
            ]);
            $account_id = $pdo->lastInsertId();

            // Inserir no convite
            $stmt = $pdo->prepare("INSERT INTO convites_usuarios 
            (account_id, nome, email, telefone, documento, tipo_pessoa, token, senha,
            pode_gerenciar_viagens, pode_gerenciar_abastecimentos, pode_gerenciar_manutencoes,
            pode_gerenciar_clientes, pode_gerenciar_usuarios, pode_ver_relatorios, status)
            VALUES 
            (:account_id, :nome, :email, :telefone, :documento, :tipo_pessoa, :token, :senha,
            1,1,1,1,1,1,'pendente')");

            $stmt->execute([
                'account_id' => $account_id,
                'nome' => $nome,
                'email' => $email,
                'telefone' => $telefone,
                'documento' => $documento,
                'tipo_pessoa' => $tipo_pessoa,
                'token' => $token,
                'senha' => $senha_hash
            ]);

            // Enviar e-mail
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $smtp_host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtp_user;
            $mail->Password   = $smtp_pass;
            $mail->SMTPSecure = $smtp_secure;
            $mail->Port       = $smtp_port;

            $mail->setFrom($smtp_from_email, $smtp_from_name);
            $mail->addAddress($email, $nome);

            $link = "https://www.gestortruck.com.br/confirmar_email.php?token=" . $token;

            $mail->isHTML(true);
            $mail->Subject = 'Confirmação de Cadastro - Gestor Truck';
            $mail->Body = "
                <h2>Olá, $nome</h2>
                <p>Obrigado por iniciar seu cadastro no <strong>Gestor Truck</strong>.</p>
                <p>Por favor, confirme seu e-mail clicando no botão abaixo:</p>
                <p>
                    <a href='$link' style='padding:10px 20px; background-color:#2563eb; color:white; text-decoration:none; border-radius:5px;'>
                        Confirmar Cadastro
                    </a>
                </p>
                <p>Ou copie e cole este link:<br>$link</p>
                <br><p>Equipe Gestor Truck</p>";

            $mail->send();

            $mensagem = "✔️ Cadastro iniciado! Enviamos um e-mail para <strong>$email</strong> com o link de confirmação.";

        } catch (Exception $e) {
            $erro = "❌ Erro: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Cadastro Iniciado - Gestor Truck</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <style>
    @keyframes moverCaminhao {
      0%, 100% { transform: translateX(0); }
      50% { transform: translateX(10px); }
    }
    .caminhao-animado {
      animation: moverCaminhao 2s infinite ease-in-out;
    }
    .bg-login {
      background-image: url('assets/img/login_bg_truck.png');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      height: 100vh;
      width: 100vw;
    }
  </style>
</head>

<body class="bg-login flex items-center justify-end">

  <div class="bg-white rounded-2xl shadow-lg w-full max-w-md p-8 mr-[15%]">
    <div class="bg-blue-700 text-white rounded-lg p-6 text-center mb-6">
      <div class="flex justify-center mb-2">
        <i class="ph ph-truck caminhao-animado text-5xl"></i>
      </div>
      <h1 class="text-2xl font-bold">Gestor Truck</h1>
      <p class="text-sm">Confirmação de Cadastro</p>
    </div>

    <?php if ($mensagem): ?>
      <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4 text-center">
        <p class="text-lg font-semibold mb-2">✔️ Cadastro iniciado com sucesso!</p>
        <p><?= $mensagem ?></p>
      </div>
      <a href="login.php" class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-2 rounded-md">
        Ir para Login
      </a>
    <?php elseif ($erro): ?>
      <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4 text-center">
        <p class="text-lg font-semibold mb-2">❌ Ocorreu um erro.</p>
        <p><?= htmlspecialchars($erro); ?></p>
        <a href="register.php" class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-2 rounded-md mt-4">
          Voltar para Cadastro
        </a>
      </div>
    <?php endif; ?>
  </div>

</body>
</html>
