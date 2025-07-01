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
<div id="drawer" class="fixed top-0 right-0 w-1/2 h-full bg-white shadow-lg transform translate-x-full transition-transform duration-300 z-50">
  <div class="flex justify-between items-center p-4 border-b">
    <h2 class="text-xl font-bold" id="drawerTitle">Novo Motorista</h2>
    <button onclick="closeDrawer()"><i class="ph ph-x text-xl"></i></button>
  </div>
  <div class="p-4">
    <form id="formMotorista" action="processa_motorista.php" method="POST" class="space-y-4 overflow-y-auto h-[90vh] pr-2">
      <div>
        <label class="block text-sm font-medium">Nome *</label>
        <input type="text" name="nome" id="nome" required class="w-full border rounded-md px-3 py-2">
        <span class="text-red-500 text-sm hidden">Campo obrigatório</span>
      </div>
      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-sm font-medium">CPF *</label>
          <input type="text" name="cpf" id="cpf" required class="w-full border rounded-md px-3 py-2">
          <span class="text-red-500 text-sm hidden">Campo obrigatório</span>
        </div>
        <div>
          <label class="block text-sm font-medium">RG</label>
          <input type="text" name="rg" id="rg" class="w-full border rounded-md px-3 py-2">
        </div>
      </div>
      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-sm font-medium">Data de Nascimento *</label>
          <input type="date" name="data_nascimento" id="data_nascimento" required class="w-full border rounded-md px-3 py-2">
        <span class="text-red-500 text-sm hidden">Campo obrigatório</span>
        </div>
        <div>
          <label class="block text-sm font-medium">Telefone *</label>
          <input type="text" name="telefone" id="telefone" required class="w-full border rounded-md px-3 py-2">
        <span class="text-red-500 text-sm hidden">Campo obrigatório</span>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-sm font-medium">Telefone Emergência</label>
          <input type="text" name="telefone_emergencia" id="telefone_emergencia" class="w-full border rounded-md px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-medium">Email *</label>
          <input type="email" name="email" id="email" required class="w-full border rounded-md px-3 py-2">
        <span class="text-red-500 text-sm hidden">Campo obrigatório</span>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-sm font-medium">CNH *</label>
          <input type="text" name="cnh" id="cnh" required class="w-full border rounded-md px-3 py-2">
        <span class="text-red-500 text-sm hidden">Campo obrigatório</span>
        </div>
        <div>
          <label class="block text-sm font-medium">Categoria CNH *</label>
          <input type="text" name="categoria_cnh" id="categoria_cnh" required class="w-full border rounded-md px-3 py-2">
        <span class="text-red-500 text-sm hidden">Campo obrigatório</span>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-sm font-medium">Validade CNH *</label>
          <input type="date" name="validade_cnh" id="validade_cnh" required class="w-full border rounded-md px-3 py-2">
        <span class="text-red-500 text-sm hidden">Campo obrigatório</span>
        </div>
        <div>
          <label class="block text-sm font-medium">Data de Admissão *</label>
          <input type="date" name="data_admissao" id="data_admissao" required class="w-full border rounded-md px-3 py-2">
        <span class="text-red-500 text-sm hidden">Campo obrigatório</span>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-sm font-medium">CPF</label>
          <input type="text" name="cpf" id="cpf" required class="w-full border rounded-md px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-medium">RG</label>
          <input type="text" name="rg" id="rg" class="w-full border rounded-md px-3 py-2">
        </div>
      </div>
      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-sm font-medium">Data de Nascimento</label>
          <input type="date" name="data_nascimento" id="data_nascimento" required class="w-full border rounded-md px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-medium">Telefone</label>
          <input type="text" name="telefone" id="telefone" required class="w-full border rounded-md px-3 py-2">
        </div>
      </div>
      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-sm font-medium">Telefone Emergência</label>
          <input type="text" name="telefone_emergencia" id="telefone_emergencia" class="w-full border rounded-md px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-medium">Email</label>
          <input type="email" name="email" id="email" required class="w-full border rounded-md px-3 py-2">
        </div>
      </div>
      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-sm font-medium">CNH</label>
          <input type="text" name="cnh" id="cnh" required class="w-full border rounded-md px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-medium">Categoria CNH</label>
          <input type="text" name="categoria_cnh" id="categoria_cnh" required class="w-full border rounded-md px-3 py-2">
        </div>
      </div>
      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-sm font-medium">Validade CNH</label>
          <input type="date" name="validade_cnh" id="validade_cnh" required class="w-full border rounded-md px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-medium">Data de Admissão</label>
          <input type="date" name="data_admissao" id="data_admissao" required class="w-full border rounded-md px-3 py-2">
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium">Endereço</label>
        <input type="text" name="endereco" id="endereco" required class="w-full border rounded-md px-3 py-2">
      </div>
      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-sm font-medium">Bairro</label>
          <input type="text" name="bairro" id="bairro" required class="w-full border rounded-md px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-medium">Cidade</label>
          <input type="text" name="cidade" id="cidade" required class="w-full border rounded-md px-3 py-2">
        </div>
      </div>
      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-sm font-medium">Estado</label>
          <input type="text" name="estado" id="estado" required class="w-full border rounded-md px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-medium">CEP</label>
          <input type="text" name="cep" id="cep" required class="w-full border rounded-md px-3 py-2">
        </div>
      </div>
      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-sm font-medium">Banco</label>
          <input type="text" name="banco" id="banco" class="w-full border rounded-md px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-medium">Agência</label>
          <input type="text" name="agencia" id="agencia" class="w-full border rounded-md px-3 py-2">
        </div>
      </div>
      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-sm font-medium">Conta</label>
          <input type="text" name="conta" id="conta" class="w-full border rounded-md px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-medium">PIX</label>
          <input type="text" name="pix" id="pix" class="w-full border rounded-md px-3 py-2">
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium">Observações</label>
        <textarea name="observacoes" id="observacoes" class="w-full border rounded-md px-3 py-2"></textarea>
      </div>
      <div>
        <label class="block text-sm font-medium">Endereço *</label>
        <input type="text" name="endereco" id="endereco" required class="w-full border rounded-md px-3 py-2">
        <span class="text-red-500 text-sm hidden">Campo obrigatório</span>
      </div>
      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-sm font-medium">Bairro *</label>
          <input type="text" name="bairro" id="bairro" required class="w-full border rounded-md px-3 py-2">
          <span class="text-red-500 text-sm hidden">Campo obrigatório</span>
        </div>
        <div>
          <label class="block text-sm font-medium">Cidade *</label>
          <input type="text" name="cidade" id="cidade" required class="w-full border rounded-md px-3 py-2">
          <span class="text-red-500 text-sm hidden">Campo obrigatório</span>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-sm font-medium">Estado *</label>
          <input type="text" name="estado" id="estado" required class="w-full border rounded-md px-3 py-2">
          <span class="text-red-500 text-sm hidden">Campo obrigatório</span>
        </div>
        <div>
          <label class="block text-sm font-medium">CEP *</label>
          <input type="text" name="cep" id="cep" required class="w-full border rounded-md px-3 py-2">
          <span class="text-red-500 text-sm hidden">Campo obrigatório</span>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-sm font-medium">Banco</label>
          <input type="text" name="banco" id="banco" class="w-full border rounded-md px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-medium">Agência</label>
          <input type="text" name="agencia" id="agencia" class="w-full border rounded-md px-3 py-2">
        </div>
      </div>
      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-sm font-medium">Conta</label>
          <input type="text" name="conta" id="conta" class="w-full border rounded-md px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-medium">PIX</label>
          <input type="text" name="pix" id="pix" class="w-full border rounded-md px-3 py-2">
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium">Observações</label>
        <textarea name="observacoes" id="observacoes" class="w-full border rounded-md px-3 py-2"></textarea>
      </div>
      <div>
        <label class="block text-sm font-medium">Senha de Acesso *</label>
        <input type="password" name="senha" id="senha" required class="w-full border rounded-md px-3 py-2">
        <span class="text-red-500 text-sm hidden">Campo obrigatório</span>
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

<script>
  function openDrawer() {
    document.getElementById('drawerTitle').innerText = 'Novo Motorista';
    document.getElementById('formMotorista').reset();
    document.getElementById('btnSalvar').disabled = true;
    document.querySelectorAll('#formMotorista span.text-red-500').forEach(s => s.classList.add('hidden'));
    document.getElementById('drawer').classList.remove('translate-x-full');
    document.getElementById('drawer-backdrop').classList.remove('hidden');
  }
  function closeDrawer() {
    document.getElementById('drawer').classList.add('translate-x-full');
    document.getElementById('drawer-backdrop').classList.add('hidden');
  }
  const requiredIds = ['nome','cpf','data_nascimento','telefone','email','cnh','categoria_cnh','validade_cnh','data_admissao','endereco','bairro','cidade','estado','cep','senha'];
  requiredIds.forEach(id => {
    document.getElementById(id).addEventListener('input', verificarCampos);
  });

  function verificarCampos() {
    let preenchido = true;
    requiredIds.forEach(id => {
      const val = document.getElementById(id).value.trim();
      if (!val) preenchido = false;
    });
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
    if (valid && confirm('Confirmar cadastro do motorista?')) {
      this.submit();
    }
  });
</script>
</body>
</html>
