<?php
session_start();
require_once 'db_config.php';

// Somente apos buscar convites iremos popular $motoristas
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$account_id = $_SESSION['account_id'];
$email_sessao = $_SESSION['email'];

// Verificar permissao
$stmt = $pdo->prepare("SELECT pode_gerenciar_usuarios FROM permissoes_usuarios WHERE user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$permissoes = $stmt->fetch(PDO::FETCH_ASSOC);

$temPermissao = ($permissoes && $permissoes['pode_gerenciar_usuarios'] == 1);

// Buscar convites de motoristas
$stmt = $pdo->prepare("SELECT * FROM convites_usuarios WHERE account_id = :account_id AND tipo_usuario = 'Motorista'");
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
    <?php if (!$temPermissao): ?>
      <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-red-600 mb-4">❌ Acesso negado</h2>
        <p>Você não tem permissão para acessar esta página.</p>
      </div>
    <?php else: ?>
      <?php if (isset($_GET['status']) && $_GET['status'] === 'sucesso'): ?>
        <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4">
          Convite gerado com sucesso! Verifique seu e-mail para copiar o link e enviar ao motorista.
        </div>
      <?php endif; ?>
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Gestão de Motoristas</h1>
        <button onclick="openDrawer()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md flex items-center gap-2">
          <i class="ph ph-user-plus"></i> Novo Motorista
        </button>
      </div>

      <div class="bg-white rounded-lg shadow-md p-6">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b">
              <th class="text-left p-2">Nome</th>
              <th class="text-left p-2">Status</th>
              <th class="text-right p-2">Ações</th>
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
                    <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs">Pendente</span>
                  <?php endif; ?>
                </td>
                <td class="p-2 text-right">
                  <button onclick='editMotorista(<?= json_encode($m) ?>)' class="bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded text-xs">
                    <i class="ph ph-pencil"></i> Editar
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Drawer -->
<div id="drawer" class="fixed top-0 right-0 w-96 h-full bg-white shadow-lg transform translate-x-full transition-transform duration-300 z-50">
  <div class="flex justify-between items-center p-4 border-b">
    <h2 class="text-xl font-bold" id="drawerTitle">Novo Motorista</h2>
    <button onclick="closeDrawer()"><i class="ph ph-x text-xl"></i></button>
  </div>
  <div class="p-4">
    <form id="formMotorista" action="processa_convite.php" method="POST" class="space-y-4">
      <input type="hidden" name="id_convite" id="id_convite">
      <input type="hidden" name="redirect" value="motoristas.php">
      <div>
        <label class="block text-sm font-medium">Nome do Motorista</label>
        <input type="text" name="nome" id="nome" required class="w-full border rounded-md px-3 py-2">
      </div>
      <div>
        <label class="block text-sm font-medium">Seu e-mail</label>
        <div class="flex gap-2">
          <input type="email" value="<?= htmlspecialchars($email_sessao) ?>" disabled class="flex-1 border rounded-md px-3 py-2 bg-gray-100">
          <button type="button" onclick="confirmEmail()" id="btnConfirm" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded">Confirmar e-mail</button>
        </div>
        <input type="hidden" name="email" value="<?= htmlspecialchars($email_sessao) ?>">
      </div>
      <input type="hidden" name="tipo_usuario" value="Motorista">
      <button type="submit" id="btnEnviar" disabled class="bg-gray-400 text-white px-6 py-2 rounded-md w-full cursor-not-allowed">Enviar Convite</button>
    </form>
  </div>
</div>
<div id="drawer-backdrop" onclick="closeDrawer()" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40"></div>

<script>
  function openDrawer() {
    document.getElementById('id_convite').value = '';
    document.getElementById('drawerTitle').innerText = 'Novo Motorista';
    document.getElementById('formMotorista').reset();
    const btnEnviar = document.getElementById('btnEnviar');
    const btnConfirm = document.getElementById('btnConfirm');
    btnEnviar.disabled = true;
    btnEnviar.classList.remove('bg-blue-600', 'hover:bg-blue-700', 'cursor-pointer');
    btnEnviar.classList.add('bg-gray-400', 'cursor-not-allowed');
    btnConfirm.disabled = false;
    btnConfirm.classList.remove('bg-gray-400', 'cursor-not-allowed');
    btnConfirm.classList.add('bg-blue-600', 'hover:bg-blue-700');
    document.getElementById('drawer').classList.remove('translate-x-full');
    document.getElementById('drawer-backdrop').classList.remove('hidden');
  }
  function closeDrawer() {
    document.getElementById('drawer').classList.add('translate-x-full');
    document.getElementById('drawer-backdrop').classList.add('hidden');
  }
  function confirmEmail() {
    const btnEnviar = document.getElementById('btnEnviar');
    const btnConfirm = document.getElementById('btnConfirm');
    btnEnviar.disabled = false;
    btnEnviar.classList.remove('bg-gray-400', 'cursor-not-allowed');
    btnEnviar.classList.add('bg-blue-600', 'hover:bg-blue-700');
    btnConfirm.disabled = true;
    btnConfirm.classList.remove('bg-blue-600', 'hover:bg-blue-700');
    btnConfirm.classList.add('bg-gray-400', 'cursor-not-allowed');
  }
  function editMotorista(data) {
    openDrawer();
    document.getElementById('drawerTitle').innerText = 'Editar Motorista';
    document.getElementById('nome').value = data.nome;
    document.getElementById('id_convite').value = data.id;
    const btnEnviar = document.getElementById('btnEnviar');
    const btnConfirm = document.getElementById('btnConfirm');
    btnEnviar.disabled = false;
    btnEnviar.classList.remove('bg-gray-400', 'cursor-not-allowed');
    btnEnviar.classList.add('bg-blue-600', 'hover:bg-blue-700');
    btnConfirm.disabled = true;
    btnConfirm.classList.remove('bg-blue-600', 'hover:bg-blue-700');
    btnConfirm.classList.add('bg-gray-400', 'cursor-not-allowed');
  }
</script>
</body>
</html>
