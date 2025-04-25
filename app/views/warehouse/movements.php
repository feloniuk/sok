<?php
// app/views/warehouse/movements.php - Сторінка рухів товарів
$title = 'Історія рухів товарів';

// Функції для фільтрації та пагінації
function buildFilterUrl($newParams = []) {
    $currentParams = $_GET;
    $params = array_merge($currentParams, $newParams);
    
    // Видалення порожніх параметрів
    foreach ($params as $key => $value) {
        if ($value === '' || $value === null) {
            unset($params[$key]);
        }
    }
    
    return '?' . http_build_query($params);
}

// Функція для отримання назви типу руху
function getMovementTypeName($type) {
    $types = [
        'incoming' => 'Надходження',
        'outgoing' => 'Витрата',
        'adjustment' => 'Коригування'
    ];
    
    return $types[$type] ?? $type;
}

// Функція для отримання класу типу руху
function getMovementTypeClass($type) {
    $classes = [
        'incoming' => 'success',
        'outgoing' => 'danger',
        'adjustment' => 'warning'
    ];
    
    return $classes[$type] ?? 'secondary';
}

// Підключення додаткових CSS
$extra_css = '
<style>
    .filter-card {
        border: none;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .movements-table th, .movements-table td {
        vertical-align: middle;
    }
    
    .movement-quantity {
        font-weight: bold;
    }
    
    .movement-incoming {
        color: #28a745;
    }
    
    .movement-outgoing {
        color: #dc3545;
    }
    
    .movement-adjustment {
        color: #ffc107;
    }
</style>';

// Підключення додаткових JS
$extra_js = '
<script>
    $(document).ready(function() {
        // Ініціалізація вибору дати
        $(".datepicker").datepicker({
            format: "yyyy-mm-dd",
            todayBtn: "linked",
            clearBtn: true,
            language: "uk",
            autoclose: true,
            todayHighlight: true
        });
        
        // Фільтрація рухів товарів при зміні параметрів
        $(".filter-control").on("change", function() {
            $("#filterForm").submit();
        });
        
        // Скидання фільтрів
        $("#resetFilters").on("click", function() {
            $(".filter-control").each(function() {
                if ($(this).is("select")) {
                    $(this).val("");
                } else {
                    $(this).val("");
                }
            });
            $("#filterForm").submit();
        });
    });
</script>';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Історія рухів товарів</h1>
            <a href="<?= base_url('warehouse/add_movement') ?>" class="d-none d-sm-inline-block btn btn-success shadow-sm">
                <i class="fas fa-plus-circle fa-sm text-white-50 me-1"></i> Додати рух товару
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Фільтри -->
    <div class="col-md-3 mb-4">
        <div class="card filter-card">
            <div class="card-header bg-primary text-white">
                <h5 class="m-0">Фільтри</h5>
            </div>
            <div class="card-body">
                <form id="filterForm" action="<?= base_url('warehouse/movements') ?>" method="GET">
                    <!-- Пошук -->
                    <div class="mb-3">
                        <label for="keyword" class="form-label">Пошук</label>
                        <div class="input-group">
                            <input type="text" class="form-control filter-control" id="keyword" name="keyword" value="<?= $_GET['keyword'] ?? '' ?>" placeholder="Введіть назву товару...">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Продукт -->
                    <div class="mb-3">
                        <label for="product_id" class="form-label">Продукт</label>
                        <select class="form-select filter-control" id="product_id" name="product_id">
                            <option value="">Всі продукти</option>
                            <?php foreach ($products ?? [] as $product): ?>
                                <option value="<?= $product['id'] ?>" <?= isset($_GET['product_id']) && $_GET['product_id'] == $product['id'] ? 'selected' : '' ?>>
                                    <?= $product['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Склад -->
                    <div class="mb-3">
                        <label for="warehouse_id" class="form-label">Склад</label>
                        <select class="form-select filter-control" id="warehouse_id" name="warehouse_id">
                            <option value="">Всі склади</option>
                            <?php foreach ($warehouses ?? [] as $warehouse): ?>
                                <option value="<?= $warehouse['id'] ?>" <?= isset($_GET['warehouse_id']) && $_GET['warehouse_id'] == $warehouse['id'] ? 'selected' : '' ?>>
                                    <?= $warehouse['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Тип руху -->
                    <div class="mb-3">
                        <label for="movement_type" class="form-label">Тип руху</label>
                        <select class="form-select filter-control" id="movement_type" name="movement_type">
                            <option value="">Всі типи</option>
                            <option value="incoming" <?= isset($_GET['movement_type']) && $_GET['movement_type'] == 'incoming' ? 'selected' : '' ?>>Надходження</option>
                            <option value="outgoing" <?= isset($_GET['movement_type']) && $_GET['movement_type'] == 'outgoing' ? 'selected' : '' ?>>Витрата</option>
                            <option value="adjustment" <?= isset($_GET['movement_type']) && $_GET['movement_type'] == 'adjustment' ? 'selected' : '' ?>>Коригування</option>
                        </select>
                    </div>
                    
                    <!-- Період -->
                    <div class="mb-3">
                        <label class="form-label">Період</label>
                        <div class="row g-2">
                            <div class="col">
                                <input type="text" class="form-control filter-control datepicker" name="date_from" placeholder="З" value="<?= $_GET['date_from'] ?? '' ?>">
                            </div>
                            <div class="col">
                                <input type="text" class="form-control filter-control datepicker" name="date_to" placeholder="По" value="<?= $_GET['date_to'] ?? '' ?>">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Кнопки -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i> Застосувати фільтри
                        </button>
                        <button type="button" id="resetFilters" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Скинути фільтри
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Таблиця рухів товарів -->
    <div class="col-md-9">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Рухи товарів</h6>
                <div class="dropdown">
                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-export me-1"></i> Експорт
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                        <li><a class="dropdown-item" href="<?= base_url('reports/export_movements?format=csv' . http_build_query($_GET)) ?>">CSV</a></li>
                        <li><a class="dropdown-item" href="<?= base_url('reports/export_movements?format=excel' . http_build_query($_GET)) ?>">Excel</a></li>
                        <li><a class="dropdown-item" href="<?= base_url('reports/export_movements?format=pdf' . http_build_query($_GET)) ?>">PDF</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($movements)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Рухи товарів не знайдені. Спробуйте змінити параметри фільтрації.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover movements-table">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Дата</th>
                                    <th>Продукт</th>
                                    <th>Склад</th>
                                    <th>Тип руху</th>
                                    <th>Кількість</th>
                                    <th>Примітки</th>
                                    <th>Виконавець</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($movements as $movement): ?>
                                    <tr>
                                        <td><?= $movement['id'] ?></td>
                                        <td><?= date('d.m.Y H:i', strtotime($movement['created_at'])) ?></td>
                                        <td><?= $movement['product_name'] ?></td>
                                        <td><?= $movement['warehouse_name'] ?></td>
                                        <td>
                                            <span class="badge bg-<?= getMovementTypeClass($movement['movement_type']) ?>">
                                                <?= getMovementTypeName($movement['movement_type']) ?>
                                            </span>
                                        </td>
                                        <td class="movement-quantity <?= 'movement-' . $movement['movement_type'] ?>">
                                            <?php if ($movement['quantity'] > 0): ?>
                                                +<?= $movement['quantity'] ?>
                                            <?php else: ?>
                                                <?= $movement['quantity'] ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($movement['notes'])): ?>
                                                <span data-bs-toggle="tooltip" title="<?= htmlspecialchars($movement['notes']) ?>">
                                                    <?= mb_substr($movement['notes'], 0, 30) ?><?= mb_strlen($movement['notes']) > 30 ? '...' : '' ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $movement['first_name'] . ' ' . $movement['last_name'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Пагінація -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($pagination['current_page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= buildFilterUrl(['page' => 1]) ?>">
                                            <i class="fas fa-angle-double-left"></i>
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= buildFilterUrl(['page' => $pagination['current_page'] - 1]) ?>">
                                            <i class="fas fa-angle-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php
                                $startPage = max(1, $pagination['current_page'] - 2);
                                $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
                                
                                for ($i = $startPage; $i <= $endPage; $i++):
                                ?>
                                    <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= buildFilterUrl(['page' => $i]) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= buildFilterUrl(['page' => $pagination['current_page'] + 1]) ?>">
                                            <i class="fas fa-angle-right"></i>
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= buildFilterUrl(['page' => $pagination['total_pages']]) ?>">
                                            <i class="fas fa-angle-double-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Сумарна статистика -->
        <div class="row">
            <div class="col-md-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Загальне надходження
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php
                                    $totalIncoming = 0;
                                    foreach ($movements as $movement) {
                                        if ($movement['movement_type'] == 'incoming') {
                                            $totalIncoming += $movement['quantity'];
                                        }
                                    }
                                    echo $totalIncoming;
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-arrow-circle-down fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Загальна витрата
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php
                                    $totalOutgoing = 0;
                                    foreach ($movements as $movement) {
                                        if ($movement['movement_type'] == 'outgoing') {
                                            $totalOutgoing += abs($movement['quantity']);
                                        }
                                    }
                                    echo $totalOutgoing;
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-arrow-circle-up fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Баланс
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo $totalIncoming - $totalOutgoing; ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-balance-scale fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>