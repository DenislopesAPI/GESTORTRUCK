<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'db_config.php';

$mensagem = '';
$erro = '';

$token = $_GET['token'] ?? '';

if (!$token) {
    $erro = '❌ Token inválido.';
} else {
    // Verificar se o token existe
    $stmt = $pdo->prepare("SELECT * FROM users WHERE token_reset = :token");
    $stmt->execute(['token' => $token]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        $erro = '❌ Token inválido ou expirado.';
    }
}

// Processar nova senha
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novaSenha = $_POST['senha'];
    $confirmarSenha = $_POST['confirmar_senha'];

    if ($novaSenha !== $confirmarSenha) {
        $erro = '❌ As senhas não conferem.';
    } elseif (strlen($novaSenha) < 6) {
        $erro = '❌ A senha deve ter no mínimo 6 caracteres.';
    } else {
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);

        // Atualizar senha e remover token
        $stmt = $pdo->prepare("UPDATE users SET senha = :senha, token_reset = NULL WHERE id = :id");
        $stmt->execute([
            'senha' => $senhaHash,
            'id' => $usuario['id']
        ]);

        $mensagem = '✔️ Senha redefinida com sucesso.';
        header("refresh:2;url=login.php");
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Nova Senha - Gestor Truck</title>
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
            <p class="text-sm">Definir nova senha</p>
        </div>

        <?php if ($mensagem): ?>
            <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4 text-center">
                <p class="text-lg font-semibold mb-2"><?= $mensagem ?></p>
                <p>Redirecionando para o login...</p>
            </div>
        <?php elseif ($erro): ?>
            <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4 text-center">
                <p class="text-lg font-semibold mb-2">❌ Erro</p>
                <p><?= htmlspecialchars($erro); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!$mensagem && !$erro || isset($usuario)): ?>
            <form action="definir_nova_senha.php?token=<?= htmlspecialchars($token) ?>" method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm mb-1">Nova senha</label>
                    <div class="relative">
                        <input type="password" name="senha" id="senha" required placeholder="••••••••"
                        class="w-full border rounded-md px-4 py-2 focus:outline-blue-600">
                        <i class="ph ph-eye absolute right-3 top-2.5 cursor-pointer" onclick="toggleSenha('senha', this)"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-sm mb-1">Confirmar nova senha</label>
                    <div class="relative">
                        <input type="password" name="confirmar_senha" id="confirmar_senha" required placeholder="••••••••"
                        class="w-full border rounded-md px-4 py-2 focus:outline-blue-600">
                        <i class="ph ph-eye absolute right-3 top-2.5 cursor-pointer" onclick="toggleSenha('confirmar_senha', this)"></i>
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-md">
                    Redefinir Senha
                </button>
            </form>
        <?php endif; ?>

        <p class="text-center text-sm mt-4">
            Lembrou sua senha?
            <a href="login.php" class="text-blue-600 hover:underline">Voltar para login</a>
        </p>
    </div>

    <script>
        function toggleSenha(id, el) {
            const input = document.getElementById(id);
            if (input.type === 'password') {
                input.type = 'text';
                el.classList.remove('ph-eye');
                el.classList.add('ph-eye-slash');
            } else {
                input.type = 'password';
                el.classList.remove('ph-eye-slash');
                el.classList.add('ph-eye');
            }
        }
    </script>
</body>
</html>
