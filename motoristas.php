<?php
session_start();
require_once 'db_config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$account_id = $_SESSION['account_id'];

// Verificar permissao
$stmt = $pdo->prepare("SELECT pode_gerenciar_usuarios FROM permissoes_usuarios WHERE user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$permissoes = $stmt->fetch(PDO::FETCH_ASSOC);

$temPermissao = ($permissoes && $permissoes['pode_gerenciar_usuarios'] == 1);

// Buscar motoristas cadastrados manualmente
$stmt = $pdo->prepare("SELECT * FROM motoristas WHERE account_id = :account_id");
$stmt->execute(['account_id' => $account_id]);
$motoristas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Gestão de Motoristas - Gestor Truck</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="bg-gray-100">
<div class="flex">
  <?php include 'menu_sidebar.php'; ?>

  <div class="flex-1 p-6">
    <?php if (isset($_GET['link'])): ?>
      <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4 text-center">
        <p class="font-semibold mb-2">✔️ Link gerado com sucesso!</p>
        <?php
          $url = 'https://gestortruck.com.br/cadastro_usuario.php?token=' . urlencode($_GET['link']);
          $mailLink = 'mailto:?subject=Convite%20Gestor%20Truck&body=' . urlencode('Cadastre-se pelo link: ' . $url);
          $waLink = 'https://wa.me/?text=' . urlencode('Cadastre-se no Gestor Truck: ' . $url);
        ?>
        <div class="flex justify-center gap-4">
          <a href="<?= $mailLink ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded" target="_blank">Enviar por Email</a>
          <a href="<?= $waLink ?>" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded" target="_blank">Enviar pelo WhatsApp</a>
        </div>
      </div>
    <?php endif; ?>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold">Gestão de Motoristas</h1>
      <?php if ($temPermissao): ?>
        <button onclick="openDrawer()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md flex items-center gap-2">
          <i class="ph ph-user-plus"></i> Novo Motorista
        </button>
      <?php endif; ?>
    </div>

      <div class="bg-white rounded-lg shadow-md p-6">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b">
              <th class="text-left p-2">Nome</th>
              <th class="text-left p-2">Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($motoristas as $m): ?>
              <tr class="border-b hover:bg-gray-50">
                <td class="p-2"><?= htmlspecialchars($m['nome']); ?></td>
                <td class="p-2">
                  <?php if ($m['status'] === 'ativo'): ?>
                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Ativo</span>
                  <?php else: ?>
                    <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs">Inativo</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
  </div>
</div>

<!-- Drawer -->
<div id="drawer" class="fixed top-0 right-0 w-96 h-full bg-white shadow-lg transform translate-x-full transition-transform duration-300 z-50">
  <div class="flex justify-between items-center p-4 border-b">
    <h2 class="text-xl font-bold" id="drawerTitle">Novo Motorista</h2>
    <button onclick="closeDrawer()"><i class="ph ph-x text-xl"></i></button>
  </div>
  <div class="p-4">
    <form id="formMotorista" action="processa_motorista.php" method="POST" class="space-y-4">
      <div>
        <label class="block text-sm font-medium">Nome do Motorista</label>
        <input type="text" name="nome" id="nome" required class="w-full border rounded-md px-3 py-2">
      </div>
      <div>
        <label class="block text-sm font-medium">Telefone</label>
        <input type="text" name="telefone" id="telefone" required class="w-full border rounded-md px-3 py-2">
      </div>
      <button type="submit" id="btnGerar" disabled class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md w-full">Gerar Link</button>
    </form>
  </div>
</div>
<div id="drawer-backdrop" onclick="closeDrawer()" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40"></div>

<script>
  function openDrawer() {
    document.getElementById('drawerTitle').innerText = 'Novo Motorista';
    document.getElementById('formMotorista').reset();
    document.getElementById('btnGerar').disabled = true;
    document.getElementById('drawer').classList.remove('translate-x-full');
    document.getElementById('drawer-backdrop').classList.remove('hidden');
  }
  function closeDrawer() {
    document.getElementById('drawer').classList.add('translate-x-full');
    document.getElementById('drawer-backdrop').classList.add('hidden');
  }

  document.getElementById('nome').addEventListener('input', verificarCampos);
  document.getElementById('telefone').addEventListener('input', verificarCampos);

  function verificarCampos() {
    const nome = document.getElementById('nome').value.trim();
    const telefone = document.getElementById('telefone').value.trim();
    document.getElementById('btnGerar').disabled = !(nome && telefone);
  }
</script>
</body>
</html>
