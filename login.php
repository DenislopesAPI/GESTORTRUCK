<?php
session_start();
require_once 'db_config.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        $erro = '❌ Usuário não cadastrado ou convite não confirmado.';
    } elseif (!password_verify($senha, $usuario['senha'])) {
        $erro = '❌ Senha incorreta.';
    } else {
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['account_id'] = $usuario['account_id'];
        $_SESSION['nome'] = $usuario['nome'];
        $_SESSION['email'] = $usuario['email'];

        header('Location: dashboard.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Gestor Truck</title>
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
          <p class="text-sm">Acesse sua conta para continuar</p>
      </div>

      <?php if ($erro): ?>
        <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4">
          <?= htmlspecialchars($erro); ?>
        </div>
      <?php endif; ?>

      <form action="login.php" method="POST" class="space-y-4">
        <div>
          <label class="block text-sm mb-1">E-mail</label>
          <input type="email" name="email" required placeholder="seu@email.com"
            class="w-full border rounded-md px-4 py-2 focus:outline-blue-600">
        </div>

        <div class="relative">
          <label class="block text-sm mb-1">Senha</label>
          <input type="password" name="senha" id="senha" required placeholder="••••••••"
            class="w-full border rounded-md px-4 py-2 pr-10 focus:outline-blue-600">
          <button type="button" onclick="toggleSenha()" class="absolute right-3 top-9">
            <i id="iconSenha" class="ph ph-eye text-gray-600"></i>
          </button>
          <div class="text-right mt-1">
            <a href="reset_senha.php" class="text-blue-600 text-sm hover:underline">Esqueceu a senha?</a>
          </div>
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-md">
          Entrar
        </button>
      </form>

      <p class="text-center text-sm mt-4">
          Não tem uma conta?
          <a href="register.php" class="text-blue-600 hover:underline">Cadastre-se</a>
      </p>
  </div>

<script>
function toggleSenha() {
    const input = document.getElementById('senha');
    const icon = document.getElementById('iconSenha');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('ph-eye');
        icon.classList.add('ph-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('ph-eye-slash');
        icon.classList.add('ph-eye');
    }
}
</script>

</body>
</html>
