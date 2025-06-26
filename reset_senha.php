<?php
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
    $email = $_POST['email'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        $erro = '❌ E-mail/Usuário não cadastrado.';
    } else {
        // Gerar token único
        $token = bin2hex(random_bytes(16));

        // Atualizar token no banco
        $stmt = $pdo->prepare("UPDATE users SET token_reset = :token WHERE id = :id");
        $stmt->execute(['token' => $token, 'id' => $usuario['id']]);

        // Enviar e-mail
        try {
            $mail = new PHPMailer(true);

            $mail->isSMTP();
            $mail->Host       = $smtp_host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtp_user;
            $mail->Password   = $smtp_pass;
            $mail->SMTPSecure = $smtp_secure;
            $mail->Port       = $smtp_port;

            $mail->setFrom($smtp_from_email, $smtp_from_name);
            $mail->addAddress($email, $usuario['nome']);

            $link = "https://www.gestortruck.com.br/definir_nova_senha.php?token=" . $token;

            $mail->isHTML(true);
            $mail->Subject = 'Reset de Senha - Gestor Truck';
            $mail->Body = "
                <h2>Olá, {$usuario['nome']}</h2>
                <p>Recebemos uma solicitação para redefinir sua senha no <strong>Gestor Truck</strong>.</p>
                <p>Clique no botão abaixo para criar uma nova senha:</p>
                <p>
                    <a href='$link' style='padding:10px 20px; background-color:#2563eb; color:white; text-decoration:none; border-radius:5px;'>
                        Redefinir Senha
                    </a>
                </p>
                <p>Ou copie e cole este link no seu navegador:<br>$link</p>
                <br><p>Equipe Gestor Truck</p>";

            $mail->send();

            $mensagem = '✔️ Um e-mail foi enviado para reset de senha.';
            header("refresh:2;url=login.php");
        } catch (Exception $e) {
            $erro = "❌ Erro ao enviar e-mail: {$mail->ErrorInfo}";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Reset de Senha - Gestor Truck</title>
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
          <p class="text-sm">Resetar sua senha</p>
      </div>

      <?php if ($mensagem): ?>
        <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4">
          <?= htmlspecialchars($mensagem); ?>
        </div>
      <?php endif; ?>

      <?php if ($erro): ?>
        <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4">
          <?= htmlspecialchars($erro); ?>
        </div>
      <?php endif; ?>

      <form action="reset_senha.php" method="POST" class="space-y-4">
        <div>
          <label class="block text-sm mb-1">Informe seu e-mail</label>
          <input type="email" name="email" required placeholder="seu@email.com"
            class="w-full border rounded-md px-4 py-2 focus:outline-blue-600">
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-md">
          Enviar Link de Reset
        </button>
      </form>

      <p class="text-center text-sm mt-4">
          Lembrou sua senha?
          <a href="login.php" class="text-blue-600 hover:underline">Voltar para login</a>
      </p>
  </div>

</body>
</html>
