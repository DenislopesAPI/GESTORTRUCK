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
  <style>
    .required-label::after { content: ' *'; color: red; }
  </style>
</head>
<body class="bg-gray-100">
<div class="flex">
  <?php include 'menu_sidebar.php'; ?>

  <div class="flex-1 p-6">
    <?php if (isset($_GET['sucesso'])): ?>
      <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4 text-center">
        <p class="font-semibold">✔️ Motorista cadastrado com sucesso!</p>
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
                    <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs">Inativo</span>
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
  </div>
</div>

<!-- Drawer -->
<div id="drawer" class="fixed top-0 right-0 w-1/2 h-full bg-white shadow-lg transform translate-x-full transition-transform duration-300 z-50">
  <div class="flex justify-between items-center p-4 border-b">
    <h2 class="text-xl font-bold" id="drawerTitle">Novo Motorista</h2>
    <button onclick="closeDrawer()"><i class="ph ph-x text-xl"></i></button>
  </div>
  <div class="p-4">
      <form id="formMotorista" action="processa_motorista.php" method="POST" class="space-y-4 overflow-y-auto h-[90vh] pr-2">
        <input type="hidden" name="id_motorista" id="id_motorista">
        <input type="hidden" name="acao" id="acao" value="salvar">
      <div>
        <label class="block text-sm font-medium required-label">Nome</label>
        <input type="text" name="nome" id="nome" class="w-full border rounded-md px-3 py-2">
        <span class="text-red-500 text-sm hidden">Campo obrigatório</span>
      </div>
      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-sm font-medium required-label">CPF</label>
          <input type="text" name="cpf" id="cpf" maxlength="14" oninput="mascaraCPF(this)" class="w-full border rounded-md px-3 py-2">
          <span class="text-red-500 text-sm hidden">Campo obrigatório</span>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-sm font-medium required-label">CNH</label>
          <input type="text" name="cnh" id="cnh" class="w-full border rounded-md px-3 py-2">
          <span class="text-red-500 text-sm hidden">Campo obrigatório</span>
        </div>
        <div>
          <label class="block text-sm font-medium required-label">Categoria CNH</label>
          <input type="text" name="categoria_cnh" id="categoria_cnh" class="w-full border rounded-md px-3 py-2">
          <span class="text-red-500 text-sm hidden">Campo obrigatório</span>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-sm font-medium required-label">Validade CNH</label>
          <input type="date" name="validade_cnh" id="validade_cnh" class="w-full border rounded-md px-3 py-2">
          <span class="text-red-500 text-sm hidden">Campo obrigatório</span>
        </div>
        <div>
          <label class="block text-sm font-medium required-label">Telefone</label>
          <input type="text" name="telefone" id="telefone" maxlength="15" oninput="mascaraTelefone(this)" class="w-full border rounded-md px-3 py-2">
          <span class="text-red-500 text-sm hidden">Campo obrigatório</span>
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium">Endereço</label>
        <input type="text" name="endereco" id="endereco" class="w-full border rounded-md px-3 py-2">
      </div>
      <div>
        <label class="block text-sm font-medium">Observação</label>
        <textarea name="observacao" id="observacao" class="w-full border rounded-md px-3 py-2"></textarea>
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
      <button type="submit" id="btnSalvar" disabled class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md w-full">Salvar</button>
    </form>
  </div>
</div>
<div id="drawer-backdrop" onclick="closeDrawer()" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40"></div>

<!-- Modal Remoção -->
<div id="modalRemover" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-sm p-6">
    <h2 class="text-xl font-semibold mb-4">Remover Motorista</h2>
    <p class="mb-6">
      Tem certeza que deseja remover este motorista?<br>
      <strong class="text-red-600">Esta ação não poderá ser desfeita.</strong>
    </p>
    <form action="processa_motorista.php" method="POST">
      <input type="hidden" name="id_motorista" id="id_motorista_remover">
      <input type="hidden" name="acao" value="remover">
      <div class="flex justify-end gap-4">
        <button type="button" onclick="fecharModalRemover()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded">
          Cancelar
        </button>
        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded">
          Remover
        </button>
      </div>
    </form>
</div>
</div>

<!-- Modal Confirmação -->
<div id="modalConfirm" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-sm p-6">
    <h2 class="text-xl font-semibold mb-4">Confirmar Cadastro</h2>
    <p class="mb-6">Deseja realmente salvar o motorista?</p>
    <div class="flex justify-end gap-4">
      <button type="button" onclick="fecharModalConfirm()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded">Cancelar</button>
      <button type="button" onclick="enviarFormulario()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded">Confirmar</button>
    </div>
  </div>
</div>

<script>
  function openDrawer() {
    document.getElementById('drawerTitle').innerText = 'Novo Motorista';
    document.getElementById('formMotorista').reset();
    document.getElementById('id_motorista').value = '';
    document.getElementById('acao').value = 'salvar';
    document.getElementById('btnSalvar').disabled = true;
    document.querySelectorAll('#formMotorista span.text-red-500').forEach(s => s.classList.add('hidden'));
    document.getElementById('drawer').classList.remove('translate-x-full');
    document.getElementById('drawer-backdrop').classList.remove('hidden');
  }

  function editMotorista(data) {
    openDrawer();
    document.getElementById('drawerTitle').innerText = 'Editar Motorista';
    document.getElementById('id_motorista').value = data.id;
    document.getElementById('acao').value = 'editar';
    document.getElementById('nome').value = data.nome;
    document.getElementById('cpf').value = data.cpf;
    document.getElementById('telefone').value = data.telefone;
    document.getElementById('cnh').value = data.numero_cnh;
    document.getElementById('categoria_cnh').value = data.categoria_cnh;
    document.getElementById('validade_cnh').value = data.validade_cnh;
    document.getElementById('endereco').value = data.endereco ?? '';
    document.getElementById('observacao').value = data.observacao ?? '';
    document.getElementById('pode_gerenciar_viagens').checked = data.pode_gerenciar_viagens == 1;
    document.getElementById('pode_gerenciar_abastecimentos').checked = data.pode_gerenciar_abastecimentos == 1;
    document.getElementById('pode_gerenciar_manutencoes').checked = data.pode_gerenciar_manutencoes == 1;
    document.getElementById('pode_gerenciar_clientes').checked = data.pode_gerenciar_clientes == 1;
    document.getElementById('pode_gerenciar_usuarios').checked = data.pode_gerenciar_usuarios == 1;
    document.getElementById('pode_ver_relatorios').checked = data.pode_ver_relatorios == 1;
    verificarCampos();
  }

  function closeDrawer() {
    document.getElementById('drawer').classList.add('translate-x-full');
    document.getElementById('drawer-backdrop').classList.add('hidden');
  }

  const requiredIds = ['nome','cpf','telefone','cnh','categoria_cnh','validade_cnh'];
  requiredIds.forEach(id => document.getElementById(id).addEventListener('input', verificarCampos));

  function verificarCampos() {
    const preenchido = requiredIds.every(id => document.getElementById(id).value.trim() !== '');
    document.getElementById('btnSalvar').disabled = !preenchido;
  }

  document.getElementById('formMotorista').addEventListener('submit', function(e) {
    e.preventDefault();
    let valid = true;
    requiredIds.forEach(id => {
      const input = document.getElementById(id);
      const error = input.nextElementSibling;
      if (input.value.trim() === '') {
        error.classList.remove('hidden');
        valid = false;
      } else {
        error.classList.add('hidden');
      }
    });
    if (valid) {
      document.getElementById('modalConfirm').classList.remove('hidden');
    }
  });

  function fecharModalConfirm() {
    document.getElementById('modalConfirm').classList.add('hidden');
  }

  function enviarFormulario() {
    document.getElementById('modalConfirm').classList.add('hidden');
    document.getElementById('formMotorista').submit();
  }

  function mascaraCPF(el) {
    let v = el.value.replace(/\D/g, '');
    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    el.value = v;
  }

  function mascaraTelefone(el) {
    let v = el.value.replace(/\D/g, '');
    v = v.replace(/^(\d{2})(\d)/g, '($1) $2');
    v = v.replace(/(\d{4,5})(\d{4})$/, '$1-$2');
    el.value = v;
  }
</script>
</body>
</html>
