<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Gestor Truck</title>
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
        .bg-register {
            background-image: url('assets/img/login_bg_truck.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
            width: 100vw;
        }
    </style>
</head>
<body class="bg-register flex items-center justify-end">

    <div class="bg-white rounded-2xl shadow-lg w-full max-w-md p-8 mr-[15%]">
        <div class="bg-blue-700 text-white rounded-lg p-6 text-center mb-6">
            <div class="flex justify-center mb-2">
                <i class="ph ph-truck caminhao-animado text-5xl"></i>
            </div>
            <h1 class="text-2xl font-bold">Gestor Truck</h1>
            <p class="text-sm">Crie sua conta e comece a gerenciar seus transportes</p>
        </div>

        <form action="processa_cadastro.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Tipo de Cadastro*</label>
                <div class="flex gap-2">
                    <button type="button" id="btnCPF" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md" onclick="setTipo('cpf')">Pessoa Física</button>
                    <button type="button" id="btnCNPJ" class="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded-md" onclick="setTipo('cnpj')">Pessoa Jurídica</button>
                </div>
            </div>

            <input type="hidden" name="tipoCadastro" id="tipoCadastro" value="cpf">
            <input type="text" name="nome" placeholder="Nome completo ou Razão Social" required class="w-full px-4 py-2 border rounded-md">
            <input type="email" name="email" placeholder="Email" required class="w-full px-4 py-2 border rounded-md">
            <input type="text" name="telefone" id="telefone" placeholder="Celular" required class="w-full px-4 py-2 border rounded-md">
            <input type="text" name="documento" id="documento" placeholder="CPF" required class="w-full px-4 py-2 border rounded-md">

            <div class="relative">
                <input type="password" name="senha" id="senha" placeholder="Senha" required class="w-full px-4 py-2 border rounded-md">
                <i class="ph ph-eye absolute right-3 top-3 cursor-pointer" onclick="toggleSenha('senha', this)"></i>
            </div>

            <div class="relative">
                <input type="password" name="confirmar_senha" id="confirmar_senha" placeholder="Confirmar Senha" required class="w-full px-4 py-2 border rounded-md">
                <i class="ph ph-eye absolute right-3 top-3 cursor-pointer" onclick="toggleSenha('confirmar_senha', this)"></i>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" required>
                <label class="text-sm">Concordo com os termos</label>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700">
                Criar Conta
            </button>
        </form>

        <p class="text-center text-sm mt-4">
            Já tem conta?
            <a href="login.php" class="text-blue-600 hover:underline">Fazer login</a>
        </p>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        let tipo = 'cpf';
        const btnCPF = document.getElementById('btnCPF');
        const btnCNPJ = document.getElementById('btnCNPJ');
        const tipoCadastro = document.getElementById('tipoCadastro');
        const documento = document.getElementById('documento');
        const telefone = document.getElementById('telefone');

        function setTipo(valor) {
            tipo = valor;
            tipoCadastro.value = valor;
            if (valor === 'cpf') {
                btnCPF.classList.add('bg-blue-600', 'text-white');
                btnCPF.classList.remove('bg-gray-200', 'text-gray-700');
                btnCNPJ.classList.add('bg-gray-200', 'text-gray-700');
                btnCNPJ.classList.remove('bg-blue-600', 'text-white');
                documento.placeholder = 'CPF';
                documento.value = '';
            } else {
                btnCNPJ.classList.add('bg-blue-600', 'text-white');
                btnCNPJ.classList.remove('bg-gray-200', 'text-gray-700');
                btnCPF.classList.add('bg-gray-200', 'text-gray-700');
                btnCPF.classList.remove('bg-blue-600', 'text-white');
                documento.placeholder = 'CNPJ';
                documento.value = '';
            }
        }

        window.setTipo = setTipo;

        window.toggleSenha = function (id, el) {
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
        };

        documento.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            if (tipo === 'cpf') {
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            } else {
                value = value.replace(/(\d{2})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1/$2');
                value = value.replace(/(\d{4})(\d{1,2})$/, '$1-$2');
            }
            e.target.value = value;
        });

        telefone.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{2})(\d)/, '($1) $2');
            value = value.replace(/(\d{5})(\d)/, '$1-$2');
            value = value.substring(0, 15);
            e.target.value = value;
        });
    });
</script>

</body>
</html>
