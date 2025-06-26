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
    $stmt = $pdo->prepare("SELECT * FROM convites_usuarios WHERE token = :token AND status = 'pendente'");
    $stmt->execute(['token' => $token]);
    $convite = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$convite) {
        $erro = '❌ Token inválido ou já utilizado.';
    } else {
        try {
            // Inserir usuário na tabela users
            $stmt = $pdo->prepare("INSERT INTO users (account_id, nome, email, senha) 
            VALUES (:account_id, :nome, :email, :senha)");
            $stmt->execute([
                'account_id' => $convite['account_id'],
                'nome'       => $convite['nome'],
                'email'      => $convite['email'],
                'senha'      => $convite['senha']
            ]);

            // Obter ID do novo usuário
            $user_id = $pdo->lastInsertId();

            // Inserir permissões padrão (todas liberadas)
            $stmt = $pdo->prepare("INSERT INTO permissoes_usuarios 
            (user_id, pode_gerenciar_viagens, pode_gerenciar_abastecimentos, 
            pode_gerenciar_manutencoes, pode_gerenciar_clientes, 
            pode_gerenciar_usuarios, pode_ver_relatorios) 
            VALUES 
            (:user_id, 1, 1, 1, 1, 1, 1)");
            $stmt->execute(['user_id' => $user_id]);

            // Atualizar status do convite para ativo
            $stmt = $pdo->prepare("UPDATE convites_usuarios SET status = 'ativo' WHERE id = :id");
            $stmt->execute(['id' => $convite['id']]);

            $mensagem = "✔️ Cadastro confirmado com sucesso! Agora você pode acessar o sistema normalmente.";
            header("refresh:2;url=login.php");

        } catch (Exception $e) {
            $erro = '❌ Erro ao confirmar cadastro: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Confirmação de Cadastro - Gestor Truck</title>
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
                <p class="text-lg font-semibold mb-2"><?= $mensagem ?></p>
                <p>Redirecionando para o login...</p>
            </div>
        <?php elseif ($erro): ?>
            <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4 text-center">
                <p class="text-lg font-semibold mb-2">❌ Ocorreu um erro.</p>
                <p><?= htmlspecialchars($erro); ?></p>
                <a href="login.php" class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-2 rounded-md mt-4">
                    Ir para Login
                </a>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>
