<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once('db_config.php');
require_once(__DIR__ . '/../secrets_keys/keys.php');

// Configurar fuso horário para Brasília
date_default_timezone_set('America/Sao_Paulo');

// Mapeamento das situações por código
$situacoes_map = [
    '0' => 'Aberta', '1' => 'Faturada', '2' => 'Cancelada', '3' => 'Aprovada',
    '4' => 'Preparando Envio', '5' => 'Enviada', '6' => 'Entregue',
    '7' => 'Pronto Envio', '8' => 'Dados Incompletos', '9' => 'Não Entregue'
];

$user_id = $_SESSION['user_id'] ?? 1;
$mensagem_erro = '';

$tipo = $_GET['filtro'] ?? 'hoje';
$range = $_GET['range'] ?? '';
$pagina_atual = max(1, intval($_GET['pagina'] ?? 1));
$por_pagina = 50;

$hoje_obj = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
$hoje = $hoje_obj->format('Y-m-d');
$data_inicio = $hoje;
$data_fim = $hoje;

switch ($tipo) {
    case '7':
        $data_inicio_obj = new DateTime('-6 days', new DateTimeZone('America/Sao_Paulo'));
        $data_inicio = $data_inicio_obj->format('Y-m-d');
        $data_fim = $hoje;
        break;
    case '15':
        $data_inicio_obj = new DateTime('-14 days', new DateTimeZone('America/Sao_Paulo'));
        $data_inicio = $data_inicio_obj->format('Y-m-d');
        $data_fim = $hoje;
        break;
    case '30':
        $data_inicio_obj = new DateTime('-29 days', new DateTimeZone('America/Sao_Paulo'));
        $data_inicio = $data_inicio_obj->format('Y-m-d');
        $data_fim = $hoje;
        break;
    case 'personalizado':
        if (strpos($range, ' a ') !== false) {
            [$range_inicio_raw, $range_fim_raw] = explode(' a ', $range);
            try {
                $data_inicio_obj = new DateTime($range_inicio_raw, new DateTimeZone('America/Sao_Paulo'));
                $data_fim_obj = new DateTime($range_fim_raw, new DateTimeZone('America/Sao_Paulo'));
                $data_inicio = $data_inicio_obj->format('Y-m-d');
                $data_fim = $data_fim_obj->format('Y-m-d');
            } catch (Exception $e) {
                $mensagem_erro = 'Formato de data inválido no período personalizado.';
            }
        }
        break;
    default:
        $tipo = 'hoje';
        $data_inicio = $hoje;
        $data_fim = $hoje;
}

function chamarApiTinyV3(string $endpoint, array $params = []) {
    global $pdo, $user_id;
    $stmt = $pdo->prepare("SELECT tiny_access_token FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $tokens = $stmt->fetch(PDO::FETCH_ASSOC);
    if (empty($tokens['tiny_access_token'])) {
        return ['status' => 'erro', 'mensagem' => 'Access Token não encontrado.'];
    }
    $access_token = $tokens['tiny_access_token'];
    $query = http_build_query($params);
    $url = "https://erp.tiny.com.br/public-api/v3" . $endpoint . "?" . $query;
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ["Authorization: Bearer " . $access_token, "Accept: application/json"]
    ]);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http_code === 200) {
        return ['status' => 'sucesso', 'dados' => json_decode($response, true)];
    }
    return ['status' => 'erro', 'mensagem' => "Erro na API ({$http_code})", 'resposta' => $response];
}

$todos_pedidos = [];
$offset = 0;
$limit = 100;
if (empty($mensagem_erro)) {
    do {
        $res = chamarApiTinyV3("/pedidos", [
            'dataInicial' => $data_inicio,
            'dataFinal' => $data_fim,
            'limit' => $limit,
            'offset' => $offset,
            'orderBy' => 'desc',
            'expand' => 'notasFiscais'
        ]);
        if ($res['status'] !== 'sucesso') {
            $mensagem_erro = $res['mensagem'] ?? 'Erro inesperado';
            break;
        }
        $lote = $res['dados']['itens'] ?? [];
        $todos_pedidos = array_merge($todos_pedidos, $lote);
        $offset += $limit;
    } while (count($lote) === $limit);
}

if ($tipo === 'personalizado' && isset($range_inicio_raw)) {
    $todos_pedidos = array_filter($todos_pedidos, function ($p) use ($range_inicio_raw, $range_fim_raw) {
        $data = substr($p['dataCriacao'] ?? '', 0, 10);
        return ($data >= $range_inicio_raw) && ($data <= $range_fim_raw);
    });
}

$faturamento_total = 0;
$faturamento_por_canal = [];
foreach ($todos_pedidos as $pedido) {
    $valor_pedido = floatval($pedido['valor'] ?? 0);
    $faturamento_total += $valor_pedido;
    $canal = $pedido['ecommerce']['nome'] ?? 'Canal Desconhecido';
    if (!isset($faturamento_por_canal[$canal])) {
        $faturamento_por_canal[$canal] = ['total' => 0, 'quantidade' => 0];
    }
    $faturamento_por_canal[$canal]['total'] += $valor_pedido;
    $faturamento_por_canal[$canal]['quantidade']++;
}

$ordem_canais = ['Mercado Livre Fulfillment DJL', 'ML_DJL_IMPORTS', 'Shopee'];
$faturamento_ordenado = [];
foreach ($ordem_canais as $canal_ordenado) {
    if (isset($faturamento_por_canal[$canal_ordenado])) {
        $faturamento_ordenado[$canal_ordenado] = $faturamento_por_canal[$canal_ordenado];
    }
}
foreach ($faturamento_por_canal as $canal => $dados) {
    if (!isset($faturamento_ordenado[$canal])) {
        $faturamento_ordenado[$canal] = $dados;
    }
}
$faturamento_por_canal = $faturamento_ordenado;

$quantidade_total_pedidos = count($todos_pedidos);
$ticket_medio_geral = ($quantidade_total_pedidos > 0) ? $faturamento_total / $quantidade_total_pedidos : 0;

$total_paginas = ceil($quantidade_total_pedidos / $por_pagina);
$pedidos = array_slice($todos_pedidos, ($pagina_atual - 1) * $por_pagina, $por_pagina);

function formatarDataBrasilia($data_string) {
    if (empty($data_string)) return '';
    $data_limpa = trim($data_string);
    try {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_limpa)) {
            $data_obj = new DateTime($data_limpa, new DateTimeZone('America/Sao_Paulo'));
            return $data_obj->format('d/m/Y');
        }
        if (strpos($data_limpa, 'T') !== false) {
            if (substr($data_limpa, -1) === 'Z') {
                $data_obj = new DateTime($data_limpa, new DateTimeZone('UTC'));
                $data_obj->setTimezone(new DateTimeZone('America/Sao_Paulo'));
                return $data_obj->format('d/m/Y H:i');
            } elseif (preg_match('/[+-]\d{2}:?\d{2}$/', $data_limpa)) {
                $data_obj = new DateTime($data_limpa);
                $data_obj->setTimezone(new DateTimeZone('America/Sao_Paulo'));
                return $data_obj->format('d/m/Y H:i');
            } else {
                $data_obj = new DateTime($data_limpa, new DateTimeZone('UTC'));
                $data_obj->setTimezone(new DateTimeZone('America/Sao_Paulo'));
                return $data_obj->format('d/m/Y H:i');
            }
        } elseif (strpos($data_limpa, ' ') !== false) {
            $data_obj = new DateTime($data_limpa, new DateTimeZone('UTC'));
            $data_obj->setTimezone(new DateTimeZone('America/Sao_Paulo'));
            return $data_obj->format('d/m/Y H:i');
        } else {
            $data_obj = new DateTime($data_limpa, new DateTimeZone('America/Sao_Paulo'));
            return $data_obj->format('d/m/Y');
        }
    } catch (Exception $e) {
        $timestamp = strtotime($data_string);
        if ($timestamp !== false) {
            return date('d/m/Y', $timestamp);
        }
    }
    return $data_string;
}

$horario_atualizacao = (new DateTime('now', new DateTimeZone('America/Sao_Paulo')))->format('d/m/Y H:i:s');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Vendas Tiny</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<body class="bg-gray-100">
<div id="loading-overlay" class="hidden"></div>
<div>
  <div class="flex">
    <?php include 'menu_sidebar.php'; ?>
    <div class="flex-1 p-6 relative">
      <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Gestão de Vendas Tiny</h1>
        <?php if ($mensagem_erro): ?>
          <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($mensagem_erro) ?>
          </div>
        <?php endif; ?>
        <div class="bg-white rounded-xl shadow overflow-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-200">
              <tr>
                <th class="px-2 py-2 w-12"></th>
                <th class="px-4 py-2">Data Criação</th>
                <th class="px-4 py-2">Nº Pedido</th>
                <th class="px-4 py-2">Situação</th>
                <th class="px-4 py-2">Valor</th>
                <th class="px-4 py-2">Nome Ecommerce</th>
                <th class="px-4 py-2">Pedido Ecommerce</th>
                <th class="px-4 py-2">Nome Cliente</th>
                <th class="px-4 py-2">Nota Fiscal</th>
                <th class="px-4 py-2">Envio / Frete</th>
                <th class="px-4 py-2">Código Rastreamento</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <?php if (empty($pedidos)): ?>
                <tr><td colspan="11" class="text-center text-gray-500 py-10">Nenhuma venda encontrada.</td></tr>
              <?php else: ?>
                <?php foreach ($pedidos as $pedido): ?>
                  <?php
                    $nota_fiscal_numero = '';
                    $nota_fiscal_id = '';
                    if (!empty($pedido['notasFiscais']) && is_array($pedido['notasFiscais'])) {
                        $nf = $pedido['notasFiscais'][0] ?? [];
                        $nota_fiscal_numero = $nf['numero'] ?? '';
                        $nota_fiscal_id = $nf['id'] ?? '';
                    }
                  ?>
                  <tr class="pedido-row bg-white hover:bg-gray-50">
                    <td class="px-2 py-2 text-center"></td>
                    <td class="px-4 py-2"><?= formatarDataBrasilia($pedido['dataCriacao'] ?? '') ?></td>
                    <td class="px-4 py-2">
                      <div class="text-base font-medium whitespace-nowrap"><?= htmlspecialchars($pedido['numeroPedido'] ?? '') ?></div>
                      <span class="text-xs text-gray-500 whitespace-nowrap">ID: <?= htmlspecialchars($pedido['id'] ?? '') ?></span>
                    </td>
                    <td class="px-4 py-2"><?= htmlspecialchars($situacoes_map[$pedido['situacao']] ?? $pedido['situacao']) ?></td>
                    <td class="px-4 py-2 font-semibold whitespace-nowrap">R$ <?= number_format(floatval($pedido['valor'] ?? 0), 2, ',', '.') ?></td>
                    <td class="px-4 py-2"><?= htmlspecialchars($pedido['ecommerce']['nome'] ?? '') ?></td>
                    <td class="px-4 py-2"><?= htmlspecialchars($pedido['ecommerce']['numeroPedidoEcommerce'] ?? '') ?></td>
                    <td class="px-4 py-2"><?= htmlspecialchars($pedido['cliente']['nome'] ?? '') ?></td>
                    <td class="px-4 py-2">
                      <?php if ($nota_fiscal_numero || $nota_fiscal_id): ?>
                        <div class="whitespace-nowrap"><?= htmlspecialchars($nota_fiscal_numero) ?></div>
                        <div class="text-xs text-gray-500 whitespace-nowrap">ID: <?= htmlspecialchars($nota_fiscal_id) ?></div>
                      <?php else: ?>
                        <span class="text-gray-500">—</span>
                      <?php endif; ?>
                    </td>
                    <td class="px-4 py-2"><?php
    $nome_ecommerce = trim($pedido['ecommerce']['nome'] ?? '');
    $forma_envio = $pedido['transportador']['formaEnvio']['nome'] ?? '';
    $detalhe_frete = '';

    if ($nome_ecommerce === 'Mercado Livre Fulfillment DJL' || $nome_ecommerce === 'ML_DJL_IMPORTS') {
        $forma_frete_nome = trim($pedido['transportador']['formaFrete']['nome'] ?? '');
        if ($nome_ecommerce === 'Mercado Livre Fulfillment DJL') {
            $detalhe_frete = 'FULLFILMENT';
        } elseif ($forma_frete_nome === 'ENVIOS FLEX') {
            $detalhe_frete = 'FLEX';
        } else {
            $detalhe_frete = 'Coleta ML';
        }
    }
?>
  <div class="font-medium whitespace-nowrap"><?= htmlspecialchars($forma_envio) ?></div>
  <?php if (!empty($detalhe_frete)): ?>
    <span class="text-xs text-gray-600 whitespace-nowrap"><?= htmlspecialchars($detalhe_frete) ?></span>
  <?php endif; ?>
</td>
                    <td class="px-4 py-2"><?= htmlspecialchars($pedido['transportador']['codigoRastreamento'] ?? '') ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
