<?php

// Definir diretório raiz para includes
if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', dirname(dirname(__FILE__)));
}


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/**
 * Sistema Contabilidade Estrela 2.0
 * Listagem de Impostos por Usuário
 */

// Verificar se as configurações já foram incluídas
if (!defined('ROOT_DIR')) {
    require_once __DIR__ . '/../../../...../app/Config/App.php';
    require_once __DIR__ . '/../../../...../app/Config/Database.php';
    require_once __DIR__ . '/../../../...../app/Config/Auth.php';
    require_once __DIR__ . '/../../../...../app/Config/Logger.php';
}

// Verificar autenticação
Auth::requireLogin();

// Registrar acesso
Logger::activity('acesso', 'Acessou a listagem de impostos de renda (usuário)');

/**
 * Formata valor para exibição no padrão brasileiro
 * @param float $valor Valor a ser formatado
 * @return string Valor formatado
 */
function formata_valor_real($valor) {
    if (!is_numeric($valor)) {
        return '0,00';
    }
    return number_format((float)$valor, 2, ',', '.');
}

// Parâmetros de filtro e paginação
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 15;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$orderBy = isset($_GET['order_by']) ? $_GET['order_by'] : 'nome';
$orderDir = isset($_GET['order_dir']) ? $_GET['order_dir'] : 'ASC';

// Usuário atual
$usuario = $_SESSION['user_name'];

// Configurar offset para paginação
$offset = ($page - 1) * $limit;

// Construir consulta base
$sql = "SELECT i.*, 
        DATE_FORMAT(i.data, '%d/%m/%Y %H:%i') as data_formatada,
        DATE_FORMAT(i.data_pagamento_2025, '%d/%m/%Y') as pagamento_formatado
        FROM impostos i
        WHERE i.usuario = ?";
$params = [$usuario];

// Adicionar filtros à consulta
if (!empty($search)) {
    $sql .= " AND (i.nome LIKE ? OR i.cpf LIKE ? OR i.codigo LIKE ?)";
    $searchParam = "%{$search}%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if ($status === 'pagos') {
    $sql .= " AND i.status_boleto_2025 IN (1,6)";
} elseif ($status === 'pendentes') {
    $sql .= " AND i.status_boleto_2025 = 5";
} elseif ($status === 'nao_emitidos') {
    $sql .= " AND i.status_boleto_2025 = 0";
} elseif ($status === 'cortesias') {
    $sql .= " AND i.status_boleto_2025 = 8";
}

// Consulta para contagem total
$countSql = "SELECT COUNT(*) as total FROM ($sql) as subquery";
$totalImpostos = Database::selectOne($countSql, $params);
$totalImpostos = $totalImpostos['total'];

// Adicionar ordenação e limite à consulta principal
$sql .= " ORDER BY i.{$orderBy} {$orderDir} LIMIT {$offset}, {$limit}";

// Executar consulta
$impostos = Database::select($sql, $params);

// Calcular total de páginas
$totalPages = ceil($totalImpostos / $limit);

// Obter estatísticas
$stats = [
    'total' => Database::selectOne("SELECT COUNT(*) as total FROM impostos WHERE usuario = ?", [$usuario])['total'],
    'pagos' => Database::selectOne("SELECT COUNT(*) as total FROM impostos WHERE status_boleto_2025 IN (1,6) AND usuario = ?", [$usuario])['total'],
    'pendentes' => Database::selectOne("SELECT COUNT(*) as total FROM impostos WHERE status_boleto_2025 = 5 AND usuario = ?", [$usuario])['total'],
    'nao_emitidos' => Database::selectOne("SELECT COUNT(*) as total FROM impostos WHERE status_boleto_2025 = 0 AND usuario = ?", [$usuario])['total'],
    'cortesias' => Database::selectOne("SELECT COUNT(*) as total FROM impostos WHERE status_boleto_2025 = 8 AND usuario = ?", [$usuario])['total'],
    'total_recebido_boleto' => Database::selectOne("SELECT SUM(valor2025) as total FROM impostos WHERE status_boleto_2025 = 1 AND usuario = ?", [$usuario])['total'] ?: 0,
    'total_recebido_dinheiro' => Database::selectOne("SELECT SUM(valor2025) as total FROM impostos WHERE status_boleto_2025 = 6 AND usuario = ?", [$usuario])['total'] ?: 0,
    'total_a_receber' => Database::selectOne("SELECT SUM(valor2025) as total FROM impostos WHERE status_boleto_2025 = 5 AND usuario = ?", [$usuario])['total'] ?: 0
];

// Últimos pagamentos
$ultimos_pagamentos = Database::select("
    SELECT nome, valor2025, data_pagamento_2025, status_boleto_2025 
    FROM impostos 
    WHERE status_boleto_2025 IN (1,6) 
    AND usuario = ? 
    AND data_pagamento_2025 IS NOT NULL
    AND data_pagamento_2025 >= DATE_SUB(CURDATE(), INTERVAL 5 DAY)
    ORDER BY data_pagamento_2025 DESC
    LIMIT 5
", [$usuario]);

// Definir status para exibição
$statusLabels = [
    '0' => '<span class="badge bg-info text-white"><i class="fas fa-clock me-1"></i> BOLETO NÃO EMITIDO</span>',
    '1' => '<span class="badge bg-success text-white"><i class="fas fa-check-circle me-1"></i> BOLETO PAGO</span>',
    '5' => '<span class="badge bg-danger text-white"><i class="fas fa-sync-alt fa-spin me-1"></i> ESPERANDO PAGAMENTO</span>',
    '6' => '<span class="badge bg-success text-white"><i class="fas fa-money-bill me-1"></i> PAGAMENTO EM DINHEIRO</span>',
    '8' => '<span class="badge bg-secondary text-white"><i class="fas fa-gift me-1"></i> CORTESIA</span>'
];

// Definir tipo de pagamento para a tabela de últimos pagamentos
$tipoPagamento = [
    '1' => '<span class="badge bg-success">Boleto</span>',
    '6' => '<span class="badge bg-primary">Dinheiro</span>'
];

// Verificar mensagens de sucesso ou erro
$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imposto de Renda - <?php echo $usuario; ?> - <?php echo SITE_NAME; ?></title>
    
    <!-- Fontes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    
    <!-- Estilo personalizado -->
    <link rel="stylesheet" href="/GED2.0/assets/css/dashboard.css">
    
    <style>
        .stats-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
            height: 100%;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-card.primary {
            border-left: 4px solid #3498db;
        }
        
        .stats-card.success {
            border-left: 4px solid #2ecc71;
        }
        
        .stats-card.warning {
            border-left: 4px solid #f39c12;
        }
        
        .stats-card.danger {
            border-left: 4px solid #e74c3c;
        }
        
        .stat-value {
            font-size: 1.75rem;
            font-weight: 600;
        }
        
        .text-success {
            color: #2ecc71 !important;
        }
        
        .text-danger {
            color: #e74c3c !important;
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        .sort-icon {
            font-size: 0.8rem;
            margin-left: 0.25rem;
        }
        
        .recent-payments-card {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .payment-table th, .payment-table td {
            padding: 0.75rem 1rem;
        }
        
        .payment-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
    </style>
</head>
<body data-user-type="<?php echo $_SESSION['user_type']; ?>">
    <div class="dashboard-container">
        <!-- Menu Lateral -->
        <?php include_once ROOT_PATH . '/views/partials/sidebar.php'; ?>
        
        <!-- Conteúdo Principal -->
        <div class="main-content">
            <!-- Cabeçalho -->
            <?php include_once ROOT_PATH . '/views/partials/header.php'; ?>
            
            <!-- Conteúdo da Página -->
            <div class="dashboard-content">
                <div class="container-fluid">
                    <!-- Cabeçalho da Página -->
                    <div class="page-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h1 class="page-title">Meus Clientes - Imposto de Renda</h1>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Imposto de Renda</li>
                                    </ol>
                                </nav>
                            </div>
                            <div class="col-auto">
                                <a href="viewGerarPdfIndividual.php" class="btn btn-danger" target="_blank">
                                    <i class="fas fa-file-pdf me-2"></i> Gerar PDF
                                </a>
                                <a href="viewCadastrarImposto.php" class="btn btn-primary ms-2">
                                    <i class="fas fa-plus-circle me-2"></i> Novo Cliente
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Cards de Estatísticas -->
                    <div class="row mb-4">
                        <!-- Card de Declarações -->
                        <div class="col-md-6 col-lg-3 mb-4">
                            <div class="card stats-card primary h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="card-title text-muted mb-0">Total Declarações</h6>
                                            <div class="stat-value"><?php echo $stats['total']; ?></div>
                                        </div>
                                        <div class="icon-bg rounded-circle bg-primary bg-opacity-10 p-3">
                                            <i class="fas fa-file-alt text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="mt-3 pt-3 border-top">
                                        <div class="row g-0 text-center">
                                            <div class="col">
                                                <span class="d-block fw-bold"><?php echo $stats['pagos']; ?></span>
                                                <small class="text-success">Pagos</small>
                                            </div>
                                            <div class="col">
                                                <span class="d-block fw-bold"><?php echo $stats['pendentes']; ?></span>
                                                <small class="text-danger">Pendentes</small>
                                            </div>
                                            <div class="col">
                                                <span class="d-block fw-bold"><?php echo $stats['nao_emitidos']; ?></span>
                                                <small class="text-info">Não emitidos</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Card de Valores -->
                        <div class="col-md-6 col-lg-3 mb-4">
                            <div class="card stats-card success h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="card-title text-muted mb-0">Total Recebido</h6>
                                            <div class="stat-value text-success">R$ <?php echo formata_valor_real($stats['total_recebido_boleto'] + $stats['total_recebido_dinheiro']); ?></div>
                                        </div>
                                        <div class="icon-bg rounded-circle bg-success bg-opacity-10 p-3">
                                            <i class="fas fa-money-bill-wave text-success"></i>
                                        </div>
                                    </div>
                                    <div class="mt-3 pt-3 border-top">
                                        <div class="row g-0">
                                            <div class="col">
                                                <small class="d-block text-muted">Via Boleto</small>
                                                <span class="fw-bold">R$ <?php echo formata_valor_real($stats['total_recebido_boleto']); ?></span>
                                            </div>
                                            <div class="col">
                                                <small class="d-block text-muted">Dinheiro</small>
                                                <span class="fw-bold">R$ <?php echo formata_valor_real($stats['total_recebido_dinheiro']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Card de Pendente -->
                        <div class="col-md-6 col-lg-3 mb-4">
                            <div class="card stats-card danger h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="card-title text-muted mb-0">Falta Receber</h6>
                                            <div class="stat-value text-danger">R$ <?php echo formata_valor_real($stats['total_a_receber']); ?></div>
                                        </div>
                                        <div class="icon-bg rounded-circle bg-danger bg-opacity-10 p-3">
                                            <i class="fas fa-hourglass-half text-danger"></i>
                                        </div>
                                    </div>
                                    <div class="mt-3 pt-3 border-top">
                                        <div class="progress" style="height: 8px;">
                                            <?php 
                                            $total_valor = $stats['total_recebido_boleto'] + $stats['total_recebido_dinheiro'] + $stats['total_a_receber'];
                                            $porcentagem_recebido = $total_valor > 0 ? (($stats['total_recebido_boleto'] + $stats['total_recebido_dinheiro']) / $total_valor) * 100 : 0;
                                            ?>
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $porcentagem_recebido; ?>%" aria-valuenow="<?php echo $porcentagem_recebido; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <div class="d-flex justify-content-between mt-2">
                                            <small class="text-muted"><?php echo round($porcentagem_recebido, 1); ?>% recebido</small>
                                            <small class="text-muted"><?php echo round(100 - $porcentagem_recebido, 1); ?>% pendente</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Card de Cortesias -->
                        <div class="col-md-6 col-lg-3 mb-4">
                            <div class="card stats-card warning h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="card-title text-muted mb-0">Cortesias</h6>
                                            <div class="stat-value"><?php echo $stats['cortesias']; ?></div>
                                        </div>
                                        <div class="icon-bg rounded-circle bg-warning bg-opacity-10 p-3">
                                            <i class="fas fa-gift text-warning"></i>
                                        </div>
                                    </div>
                                    <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-end">
                                        <div>
                                            <small class="text-muted d-block">Total de Clientes</small>
                                            <span class="fw-bold"><?php echo $stats['total']; ?></span>
                                        </div>
                                        <div>
                                            <span class="badge bg-warning text-dark">
                                                <?php echo round(($stats['cortesias'] / $stats['total']) * 100, 1); ?>% de cortesias
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Últimos Pagamentos e Filtros -->
                    <div class="row mb-4">
                        <div class="col-md-10 col-lg-8 mx-auto">
                            <!-- Últimos Pagamentos -->
                            <div class="card recent-payments-card mb-4">
                                <div class="card-header bg-success text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-money-bill-wave me-2"></i>
                                        Últimos Pagamentos Recebidos
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0 payment-table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 40%">Cliente</th>
                                                    <th style="width: 20%" class="text-center">Valor</th>
                                                    <th style="width: 25%" class="text-center">Data</th>
                                                    <th style="width: 15%" class="text-center">Forma</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (count($ultimos_pagamentos) > 0): ?>
                                                    <?php foreach ($ultimos_pagamentos as $pagamento): ?>
                                                        <tr>
                                                            <td>
                                                                <div class="text-truncate" style="max-width: 180px;" title="<?php echo htmlspecialchars($pagamento['nome']); ?>">
                                                                    <?php echo htmlspecialchars($pagamento['nome']); ?>
                                                                </div>
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="fw-bold">R$ <?php echo formata_valor_real($pagamento['valor2025']); ?></span>
                                                            </td>
                                                            <td class="text-center">
                                                                <?php echo date('d/m/Y', strtotime($pagamento['data_pagamento_2025'])); ?>
                                                            </td>
                                                            <td class="text-center">
                                                                <?php echo $tipoPagamento[$pagamento['status_boleto_2025']]; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center py-3">
                                                            <div class="text-muted">
                                                                <i class="fas fa-info-circle me-2"></i> Nenhum pagamento recente.
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Filtros de Busca -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Filtros</h5>
                                </div>
                                <div class="card-body">
                                    <form action="" method="get" class="row g-3">
                                        <div class="col-md-5">
                                            <label for="search" class="form-label">Buscar</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                                <input type="text" class="form-control" id="search" name="search" placeholder="Nome, CPF ou código" value="<?php echo htmlspecialchars($search); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="">Todos</option>
                                                <option value="pagos" <?php echo $status == 'pagos' ? 'selected' : ''; ?>>Pagos</option>
                                                <option value="pendentes" <?php echo $status == 'pendentes' ? 'selected' : ''; ?>>Pendentes</option>
                                                <option value="nao_emitidos" <?php echo $status == 'nao_emitidos' ? 'selected' : ''; ?>>Não Emitidos</option>
                                                <option value="cortesias" <?php echo $status == 'cortesias' ? 'selected' : ''; ?>>Cortesias</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="limit" class="form-label">Itens</label>
                                            <select class="form-select" id="limit" name="limit">
                                                <option value="15" <?php echo $limit == 15 ? 'selected' : ''; ?>>15</option>
                                                <option value="25" <?php echo $limit == 25 ? 'selected' : ''; ?>>25</option>
                                                <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50</option>
                                                <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="fas fa-filter me-2"></i> Filtrar
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tabela de Impostos -->
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="card-title mb-0">Meus Clientes</h5>
                                </div>
                                <div class="col-auto">
                                    <span class="badge bg-primary">Total: <?php echo $totalImpostos; ?> clientes</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th width="80" class="text-center">Ações</th>
                                            <th width="80">Código</th>
                                            <th>
                                                <a href="?order_by=nome&order_dir=<?php echo $orderBy == 'nome' && $orderDir == 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>&limit=<?php echo $limit; ?>" class="text-decoration-none text-dark">
                                                    Cliente/CPF
                                                    <?php if ($orderBy == 'nome'): ?>
                                                        <i class="fas fa-sort-<?php echo $orderDir == 'ASC' ? 'up' : 'down'; ?> sort-icon"></i>
                                                    <?php endif; ?>
                                                </a>
                                            </th>
                                            <th width="110" class="text-center">
                                                <a href="?order_by=valor2024&order_dir=<?php echo $orderBy == 'valor2024' && $orderDir == 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>&limit=<?php echo $limit; ?>" class="text-decoration-none text-dark">
                                                    Valor 2024
                                                    <?php if ($orderBy == 'valor2024'): ?>
                                                        <i class="fas fa-sort-<?php echo $orderDir == 'ASC' ? 'up' : 'down'; ?> sort-icon"></i>
                                                    <?php endif; ?>
                                                </a>
                                            </th>
                                            <th width="140" class="text-center">
                                                <a href="?order_by=valor2025&order_dir=<?php echo $orderBy == 'valor2025' && $orderDir == 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>&limit=<?php echo $limit; ?>" class="text-decoration-none text-dark">
                                                    Valor 2025
                                                    <?php if ($orderBy == 'valor2025'): ?>
                                                        <i class="fas fa-sort-<?php echo $orderDir == 'ASC' ? 'up' : 'down'; ?> sort-icon"></i>
                                                    <?php endif; ?>
                                                </a>
                                            </th>
                                            <th width="140" class="text-center">
                                                <a href="?order_by=vencimento&order_dir=<?php echo $orderBy == 'vencimento' && $orderDir == 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>&limit=<?php echo $limit; ?>" class="text-decoration-none text-dark">
                                                    Vencimento
                                                    <?php if ($orderBy == 'vencimento'): ?>
                                                        <i class="fas fa-sort-<?php echo $orderDir == 'ASC' ? 'up' : 'down'; ?> sort-icon"></i>
                                                    <?php endif; ?>
                                                </a>
                                            </th>
                                            <th width="160" class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($impostos) > 0): ?>
                                            <?php foreach ($impostos as $imposto): ?>
                                                <tr>
                                                    <td class="text-center">
                                                        <div class="btn-group">
                                                            <a href="viewFormAlterarImposto.php?id=<?php echo $imposto['id']; ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Editar">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            
                                                            <?php if (!in_array($imposto['status_boleto_2025'], ['1', '6', '8'])): ?>
                                                                <a href="viewBoletoCef.php?id=<?php echo $imposto['id']; ?>" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Boleto">
                                                                    <i class="fas fa-barcode"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                            
                                                            <a href="viewVisualizarArquivoImposto.php?id=<?php echo $imposto['id']; ?>" class="btn btn-sm btn-dark" data-bs-toggle="tooltip" title="Arquivos">
                                                                <i class="fas fa-folder-open"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success"><?php echo htmlspecialchars($imposto['codigo']); ?></span>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <div class="fw-medium"><?php echo htmlspecialchars($imposto['nome']); ?></div>
                                                            <small class="text-muted"><?php echo htmlspecialchars($imposto['cpf']); ?></small>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="fw-medium">R$ <?php echo formata_valor_real($imposto['valor2024']); ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="fw-medium">R$ <?php echo formata_valor_real($imposto['valor2025']); ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php echo date('d/m/Y', strtotime($imposto['vencimento'])); ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php echo $statusLabels[$imposto['status_boleto_2025']]; ?>
                                                        
                                                        <?php if ($imposto['status_boleto_2025'] == '1' || $imposto['status_boleto_2025'] == '6'): ?>
                                                            <div class="small text-muted mt-1">
                                                                <i class="far fa-calendar-alt me-1"></i> <?php echo $imposto['pagamento_formatado']; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="fas fa-info-circle me-2"></i> Nenhum cliente encontrado com os filtros atuais.
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Paginação -->
                        <?php if ($totalPages > 1): ?>
                        <div class="card-footer">
                            <nav aria-label="Navegação de página">
                                <ul class="pagination justify-content-center mb-0">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=1&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>&order_by=<?php echo $orderBy; ?>&order_dir=<?php echo $orderDir; ?>" aria-label="Primeira">
                                                <i class="fas fa-angle-double-left"></i>
                                            </a>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>&order_by=<?php echo $orderBy; ?>&order_dir=<?php echo $orderDir; ?>" aria-label="Anterior">
                                                <i class="fas fa-angle-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php

// Definir diretório raiz para includes
if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', dirname(dirname(__FILE__)));
}

                                    // Exibir no máximo 5 links de página
                                    $startPage = max(1, min($page - 2, $totalPages - 4));
                                    $endPage = min($totalPages, max($page + 2, 5));
                                    
                                    for ($i = $startPage; $i <= $endPage; $i++):
                                    ?>
                                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>&order_by=<?php echo $orderBy; ?>&order_dir=<?php echo $orderDir; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>&order_by=<?php echo $orderBy; ?>&order_dir=<?php echo $orderDir; ?>" aria-label="Próxima">
                                                <i class="fas fa-angle-right"></i>
                                            </a>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $totalPages; ?>&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>&order_by=<?php echo $orderBy; ?>&order_dir=<?php echo $orderDir; ?>" aria-label="Última">
                                                <i class="fas fa-angle-double-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Rodapé -->
            <footer class="dashboard-footer">
                <div class="container-fluid">
                    <div class="copyright">
                        GED Contabilidade Estrela &copy; <?php echo date('Y'); ?>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap Bundle com Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Script personalizado -->
    <script src="/GED2.0/assets/js/dashboard.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Atualizar relógio de Brasília
            function updateBrasiliaTime() {
                const now = new Date();
                const brasiliaTime = new Date(now.getTime() - (now.getTimezoneOffset() * 60000));
                const formattedTime = brasiliaTime.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                document.getElementById('brasilia-clock').textContent = formattedTime;
            }
            
            updateBrasiliaTime();
            setInterval(updateBrasiliaTime, 1000);
        });
    </script>
</body>
</html>