<?php
session_start();
require_once 'db_config.php';

$token = $_GET['token'] ?? '';
$erro = '';
$mensagem = '';

if (!$token) {
    $erro = '❌ Token inválido.';
} else {
    // Verificar se o convite existe e está pendente
    $stmt = $pdo->prepare("SELECT * FROM convites_usuarios WHERE token = :token AND status = 'pendente'");
    $stmt->execute(['token' => $token]);
    $convite = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$convite) {
        $erro = '❌ Token inválido, expirado ou convite já utilizado.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    if ($senha !== $confirmar_senha) {
        $erro = '❌ As senhas não conferem.';
    } elseif (strlen($senha) < 6) {
        $erro = '❌ A senha deve ter no mínimo 6 caracteres.';
    } else {
        try {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            // Criar usuário
            $stmt = $pdo->prepare("INSERT INTO users (account_id, nome, email, senha) VALUES (:account_id, :nome, :email, :senha)");
            $stmt->execute([
                'account_id' => $convite['account_id'],
                'nome' => $nome,
                'email' => $email,
                'senha' => $senhaHash
            ]);

            $user_id = $pdo->lastInsertId();

            // Inserir permissões
            $stmt = $pdo->prepare("INSERT INTO permissoes_usuarios 
                (user_id, pode_gerenciar_viagens, pode_gerenciar_abastecimentos, 
                 pode_gerenciar_manutencoes, pode_gerenciar_clientes, 
                 pode_gerenciar_usuarios, pode_ver_relatorios) 
                VALUES 
                (:user_id, :viagens, :abastecimentos, :manutencoes, :clientes, :usuarios, :relatorios)");
            $stmt->execute([
                'user_id' => $user_id,
                'viagens' => $convite['pode_gerenciar_viagens'],
                'abastecimentos' => $convite['pode_gerenciar_abastecimentos'],
                'manutencoes' => $convite['pode_gerenciar_manutencoes'],
                'clientes' => $convite['pode_gerenciar_clientes'],
                'usuarios' => $convite['pode_gerenciar_usuarios'],
                'relatorios' => $convite['pode_ver_relatorios']
            ]);

            // Atualizar status do convite
            $stmt = $pdo->prepare("UPDATE convites_usuarios SET status = 'ativo' WHERE id = :id");
            $stmt->execute(['id' => $convite['id']]);

            $mensagem = '✔️ Cadastro concluído com sucesso.';
            header("refresh:2;url=login.php");

        } catch (Exception $e) {
            $erro = 'Erro ao cadastrar: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Conta - Gestor Truck</title>
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
            <p class="text-sm">Cadastro de Conta</p>
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

        <?php if (!$mensagem && !$erro && isset($convite)): ?>
            <form action="cadastro_usuario.php?token=<?= htmlspecialchars($token) ?>" method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm mb-1">Nome</label>
                    <input type="text" name="nome" value="<?= htmlspecialchars($convite['nome']) ?>" required class="w-full border rounded-md px-4 py-2">
                </div>

                <div>
                    <label class="block text-sm mb-1">E-mail</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($convite['email']) ?>" required class="w-full border rounded-md px-4 py-2">
                </div>

                <div>
                    <label class="block text-sm mb-1">Senha</label>
                    <div class="relative">
                        <input type="password" name="senha" id="senha" required placeholder="••••••••"
                        class="w-full border rounded-md px-4 py-2">
                        <i class="ph ph-eye absolute right-3 top-2.5 cursor-pointer" onclick="toggleSenha('senha', this)"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-sm mb-1">Confirmar senha</label>
                    <div class="relative">
                        <input type="password" name="confirmar_senha" id="confirmar_senha" required placeholder="••••••••"
                        class="w-full border rounded-md px-4 py-2">
                        <i class="ph ph-eye absolute right-3 top-2.5 cursor-pointer" onclick="toggleSenha('confirmar_senha', this)"></i>
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-md">
                    Cadastrar
                </button>
            </form>
        <?php endif; ?>

        <p class="text-center text-sm mt-4">
            Já tem uma conta?
            <a href="login.php" class="text-blue-600 hover:underline">Fazer login</a>
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
