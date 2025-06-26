<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gestor Truck</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="bg-gray-100">
    <?php include 'menu_sidebar.php'; ?>

    <div class="flex-1 p-6">
        <h1 class="text-3xl font-bold mb-6">Bem-vindo ao Gestor Truck!</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center gap-4">
                    <i class="ph ph-truck text-blue-700 text-4xl"></i>
                    <div>
                        <p class="text-gray-600">Total de Viagens</p>
                        <h2 class="text-2xl font-bold">0</h2>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center gap-4">
                    <i class="ph ph-gas-pump text-blue-700 text-4xl"></i>
                    <div>
                        <p class="text-gray-600">Abastecimentos</p>
                        <h2 class="text-2xl font-bold">0</h2>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center gap-4">
                    <i class="ph ph-wrench text-blue-700 text-4xl"></i>
                    <div>
                        <p class="text-gray-600">Manutenções</p>
                        <h2 class="text-2xl font-bold">0</h2>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center gap-4">
                    <i class="ph ph-chart-bar text-blue-700 text-4xl"></i>
                    <div>
                        <p class="text-gray-600">Relatórios</p>
                        <h2 class="text-2xl font-bold">0</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-10">
            <h2 class="text-2xl font-semibold mb-4">Resumo</h2>
            <div class="bg-white rounded-lg shadow-md p-6">
                <p class="text-gray-600">Aqui você pode acompanhar um resumo geral da sua operação. Utilize o menu lateral para acessar viagens, abastecimentos, manutenções, clientes e relatórios.</p>
            </div>
        </div>
    </div>
</body>
</html>
