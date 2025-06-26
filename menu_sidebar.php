<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<div class="flex">
  <div id="sidebar" class="bg-blue-700 text-white w-52 min-h-screen transition-all duration-300 flex flex-col">
    <div>
      <div class="flex items-center justify-between p-4">
        <span id="logo-text" class="text-lg font-bold">GestorTruck</span>
        <button onclick="toggleSidebar()">
          <i id="toggle-icon" class="ph ph-caret-left text-xl"></i>
        </button>
      </div>
      <nav class="mt-6">
        <a href="dashboard.php" class="flex items-center gap-4 p-4 hover:bg-blue-800">
          <i class="ph ph-house"></i>
          <span class="menu-text">Dashboard</span>
        </a>
        <a href="viagens.php" class="flex items-center gap-4 p-4 hover:bg-blue-800">
          <i class="ph ph-truck"></i>
          <span class="menu-text">Viagens</span>
        </a>
        <a href="abastecimentos.php" class="flex items-center gap-4 p-4 hover:bg-blue-800">
          <i class="ph ph-gas-pump"></i>
          <span class="menu-text">Abastecimentos</span>
        </a>
        <a href="manutencoes.php" class="flex items-center gap-4 p-4 hover:bg-blue-800">
          <i class="ph ph-wrench"></i>
          <span class="menu-text">Manutenções</span>
        </a>
        <a href="relatorios.php" class="flex items-center gap-4 p-4 hover:bg-blue-800">
          <i class="ph ph-chart-bar"></i>
          <span class="menu-text">Relatórios</span>
        </a>
        <div class="border-t border-blue-600 my-4"></div>

        <!-- Configurações com submenu -->
        <div>
          <button onclick="toggleSubmenu()" class="flex items-center gap-4 p-4 w-full hover:bg-blue-800">
            <i class="ph ph-gear"></i>
            <span class="menu-text">Configurações</span>
            <i id="submenu-icon" class="ph ph-caret-down ml-auto"></i>
          </button>
          <div id="submenu" class="ml-8 hidden">
            <a href="usuarios.php" class="flex items-center gap-3 p-2 hover:bg-blue-800 rounded">
              <i class="ph ph-user"></i>
              <span class="menu-text">Usuários</span>
            </a>
            <a href="motoristas.php" class="flex items-center gap-3 p-2 hover:bg-blue-800 rounded">
              <i class="ph ph-steering-wheel"></i>
              <span class="menu-text">Motoristas</span>
            </a>
            <a href="clientes.php" class="flex items-center gap-3 p-2 hover:bg-blue-800 rounded">
              <i class="ph ph-users"></i>
              <span class="menu-text">Clientes</span>
            </a>
          </div>
        </div>

        <a href="logout.php" class="flex items-center gap-4 p-4 hover:bg-blue-800">
          <i class="ph ph-sign-out"></i>
          <span class="menu-text">Sair</span>
        </a>
      </nav>
    </div>

    <!-- Bloco inferior com nome e avatar -->
    <div class="p-4 border-t border-blue-600 flex items-center gap-3">
      <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center">
        <i class="ph ph-user text-blue-700 text-xl"></i>
      </div>
      <div class="menu-text">
        <div class="text-sm font-semibold">
          <?= htmlspecialchars($_SESSION['nome']); ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const logoText = document.getElementById('logo-text');
    const toggleIcon = document.getElementById('toggle-icon');

    sidebar.classList.toggle('w-52');
    sidebar.classList.toggle('w-12');

    const texts = sidebar.querySelectorAll('.menu-text');
    texts.forEach(text => {
      text.classList.toggle('hidden');
    });

    logoText.classList.toggle('hidden');

    if (sidebar.classList.contains('w-12')) {
      toggleIcon.className = 'ph ph-caret-right text-xl';
    } else {
      toggleIcon.className = 'ph ph-caret-left text-xl';
    }
  }

  function toggleSubmenu() {
    const submenu = document.getElementById('submenu');
    const submenuIcon = document.getElementById('submenu-icon');

    submenu.classList.toggle('hidden');
    submenuIcon.classList.toggle('ph-caret-down');
    submenuIcon.classList.toggle('ph-caret-up');
  }
</script>

<link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web"/>
<script src="https://cdn.tailwindcss.com"></script>
