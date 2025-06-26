<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$account_id = $_SESSION['account_id'];
$email_sessao = $_SESSION['email'];

// Verificar permissão
$stmt = $pdo->prepare("SELECT pode_gerenciar_usuarios FROM permissoes_usuarios WHERE user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$permissoes = $stmt->fetch(PDO::FETCH_ASSOC);

$temPermissao = ($permissoes && $permissoes['pode_gerenciar_usuarios'] == 1);

// Buscar usuários e convites
$stmt = $pdo->prepare("SELECT * FROM convites_usuarios WHERE account_id = :account_id");
$stmt->execute(['account_id' => $account_id]);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Gestão de Usuários - Gestor Truck</title>
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
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Gestão de Usuários</h1>
        <button onclick="openDrawer()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md flex items-center gap-2">
          <i class="ph ph-user-plus"></i> Novo Usuário
        </button>
      </div>

      <div class="bg-white rounded-lg shadow-md p-6">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b">
              <th class="text-left p-2">Nome</th>
              <th class="text-left p-2">Tipo</th>
              <th class="text-left p-2">Status</th>
              <th class="text-center p-2">Viagens</th>
              <th class="text-center p-2">Abastecimentos</th>
              <th class="text-center p-2">Manutenções</th>
              <th class="text-center p-2">Clientes</th>
              <th class="text-center p-2">Usuários</th>
              <th class="text-center p-2">Relatórios</th>
              <th class="text-right p-2">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($usuarios as $usuario): ?>
              <tr class="border-b hover:bg-gray-50">
                <td class="p-2">
                  <?= htmlspecialchars($usuario['nome']); ?>
                  <?= $usuario['email'] == $email_sessao ? '<span class="text-xs text-blue-600">(Master)</span>' : ''; ?>
                </td>
                <td class="p-2"><?= htmlspecialchars($usuario['tipo_usuario']); ?></td>
                <td class="p-2">
                  <?php if ($usuario['status'] === 'ativo'): ?>
                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Ativo</span>
                  <?php else: ?>
                    <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs">Pendente</span>
                  <?php endif; ?>
                </td>
                <td class="text-center"><?= $usuario['pode_gerenciar_viagens'] ? '✔️' : '❌'; ?></td>
                <td class="text-center"><?= $usuario['pode_gerenciar_abastecimentos'] ? '✔️' : '❌'; ?></td>
                <td class="text-center"><?= $usuario['pode_gerenciar_manutencoes'] ? '✔️' : '❌'; ?></td>
                <td class="text-center"><?= $usuario['pode_gerenciar_clientes'] ? '✔️' : '❌'; ?></td>
                <td class="text-center"><?= $usuario['pode_gerenciar_usuarios'] ? '✔️' : '❌'; ?></td>
                <td class="text-center"><?= $usuario['pode_ver_relatorios'] ? '✔️' : '❌'; ?></td>
                <td class="p-2 text-right">
                  <?php if ($usuario['email'] == $email_sessao): ?>
                    <button class="bg-gray-300 text-gray-600 px-3 py-1 rounded text-xs cursor-not-allowed" disabled>
                      <i class="ph ph-lock"></i> Master
                    </button>
                  <?php else: ?>
                    <button onclick='editUser(<?= json_encode($usuario) ?>)' 
                    class="bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded text-xs">
                      <i class="ph ph-pencil"></i> Editar
                    </button>
                  <?php endif; ?>
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
    <h2 class="text-xl font-bold" id="drawerTitle">Novo Usuário</h2>
    <button onclick="closeDrawer()"><i class="ph ph-x text-xl"></i></button>
  </div>
  <div class="p-4">
    <form id="formUsuario" action="processa_convite.php" method="POST" class="space-y-4">
      <input type="hidden" name="id_convite" id="id_convite">

      <div>
        <label class="block text-sm font-medium">Nome</label>
        <input type="text" name="nome" id="nome" required class="w-full border rounded-md px-3 py-2">
      </div>

      <div>
        <label class="block text-sm font-medium">E-mail</label>
        <input type="email" name="email" id="email" required class="w-full border rounded-md px-3 py-2">
      </div>

      <div>
        <label class="block text-sm font-medium">Tipo de Usuário</label>
        <select name="tipo_usuario" id="tipo_usuario" class="w-full border rounded-md px-3 py-2">
          <option value="Administrador">Administrador</option>
          <option value="Motorista" selected>Motorista</option>
        </select>
      </div>

      <div>
        <h3 class="text-sm font-semibold mb-2">Permissões</h3>
        <div class="grid grid-cols-1 gap-2">
          <label><input type="checkbox" name="pode_gerenciar_viagens" id="pode_gerenciar_viagens"> Gerenciar Viagens</label>
          <label><input type="checkbox" name="pode_gerenciar_abastecimentos" id="pode_gerenciar_abastecimentos"> Gerenciar Abastecimentos</label>
          <label><input type="checkbox" name="pode_gerenciar_manutencoes" id="pode_gerenciar_manutencoes"> Gerenciar Manutenções</label>
          <label><input type="checkbox" name="pode_gerenciar_clientes" id="pode_gerenciar_clientes"> Gerenciar Clientes</label>
          <label><input type="checkbox" name="pode_gerenciar_usuarios" id="pode_gerenciar_usuarios"> Gerenciar Usuários</label>
          <label><input type="checkbox" name="pode_ver_relatorios" id="pode_ver_relatorios"> Ver Relatórios</label>
        </div>
      </div>

      <input type="hidden" name="acao" id="acao" value="salvar">

      <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md w-full">
        Salvar
      </button>

      <button type="button" onclick="abrirModalRemover()" 
      class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-md w-full">
        Remover Usuário
      </button>
    </form>
  </div>
</div>

<div id="drawer-backdrop" onclick="closeDrawer()" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40"></div>

<!-- Modal Remoção -->
<div id="modalRemover" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-sm p-6">
    <h2 class="text-xl font-semibold mb-4">Remover Usuário</h2>
    <p class="mb-6">
      Tem certeza que deseja remover este usuário?<br>
      <strong class="text-red-600">Esta ação não poderá ser desfeita.</strong>
    </p>
    <form action="processa_convite.php" method="POST">
      <input type="hidden" name="id_convite" id="id_convite_remover">
      <input type="hidden" name="acao" value="remover">
      <div class="flex justify-end gap-4">
        <button type="button" onclick="fecharModalRemover()" 
        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded">
          Cancelar
        </button>
        <button type="submit" 
        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded">
          Remover
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  function openDrawer() {
    document.getElementById('drawerTitle').innerText = 'Novo Usuário';
    document.getElementById('formUsuario').reset();
    document.getElementById('id_convite').value = '';
    document.getElementById('acao').value = 'salvar';
    document.getElementById('drawer').classList.remove('translate-x-full');
    document.getElementById('drawer-backdrop').classList.remove('hidden');
  }

  function closeDrawer() {
    document.getElementById('drawer').classList.add('translate-x-full');
    document.getElementById('drawer-backdrop').classList.add('hidden');
  }

  function editUser(data) {
    openDrawer();
    document.getElementById('drawerTitle').innerText = 'Editar Usuário';
    document.getElementById('id_convite').value = data.id;
    document.getElementById('nome').value = data.nome;
    document.getElementById('email').value = data.email;
    document.getElementById('tipo_usuario').value = data.tipo_usuario;
    document.getElementById('pode_gerenciar_viagens').checked = data.pode_gerenciar_viagens == 1;
    document.getElementById('pode_gerenciar_abastecimentos').checked = data.pode_gerenciar_abastecimentos == 1;
    document.getElementById('pode_gerenciar_manutencoes').checked = data.pode_gerenciar_manutencoes == 1;
    document.getElementById('pode_gerenciar_clientes').checked = data.pode_gerenciar_clientes == 1;
    document.getElementById('pode_gerenciar_usuarios').checked = data.pode_gerenciar_usuarios == 1;
    document.getElementById('pode_ver_relatorios').checked = data.pode_ver_relatorios == 1;
  }

  function abrirModalRemover() {
    const idConvite = document.getElementById('id_convite').value;
    document.getElementById('id_convite_remover').value = idConvite;
    document.getElementById('modalRemover').classList.remove('hidden');
  }

  function fecharModalRemover() {
    document.getElementById('modalRemover').classList.add('hidden');
  }
</script>
</body>
</html>
