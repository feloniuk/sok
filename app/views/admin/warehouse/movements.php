<?php
// app/views/admin/warehouse/movements.php
$title = 'Рух товарів';

// Функции для фильтрации и пагинации
function buildFilterUrl($newParams = []) {
    $currentParams = $_GET;
    $params = array_merge($currentParams, $newParams);
    
    // Удаление пустых параметров
    foreach ($params as $key => $value) {
        if ($value === '' || $value === null) {
            unset($params[$key]);
        }
    }
    
    return '?' . http_build_query($params);
}

// Дополнительные CSS стили
$extra_css = '
<style>
    .filter-card {
        border: none;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .movements-table img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
    }
</style>';

// Дополнительные JS скрипты
$extra_js = '
<script>
$(document).ready(function() {
    // Инициализация выбора даты
    $(".datepicker").datepicker({
        format: "yyyy-mm-dd",
        todayBtn: "linked",
        clearBtn: true,
        language: "uk",
        autoclose: true,
        todayHighlight: true
    });
    
    // Фильтрация при изменении параметров
    $(".filter-control").on("change", function() {
        $("#filterForm").submit();
    });
    
    // Сброс фильтров
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
    <div class="col-md-8">
        <h1 class="h2 mb-0"><?= $title ?></h1>
        <p class="text-muted">Інформація про всі переміщення товарів на складі</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="<?= base_url('admin/warehouse/add_movement') ?>" class="btn btn-success">
            <i class="fas fa-plus-circle me-1"></i> Додати рух
        </a>
    </div>
</div>

<div class="row">
    <!-- Фильтры -->
    <div class="col-md-3 mb-4">
        <div class="card filter-card">
            <div class="card-header bg-primary text-white">
                <h5 class="m-0">Фільтри</h5>
            </div>
            <div class="card-body">
                <form id="filterForm" action="<?= base_url('admin/warehouse/movements') ?>" method="GET">
                    <!-- Поиск по ключевому слову -->
                    <div class="mb-3">
                        <label for="keyword" class="form-label">Пошук</label>
                        <input type="text" class="form-control filter-control" id="keyword" name="keyword" value="<?= $_GET['keyword'] ?? '' ?>" placeholder="Назва продукту, примітки...">
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
                    
                    <!-- Тип движения -->
                    <div class="mb-3">
                        <label for="movement_type" class="form-label">Тип руху</label>
                        <select class="form-select filter-control" id="movement_type" name="movement_type">
                            <option value="">Всі типи</option>
                            <option value="incoming" <?= isset($_GET['movement_type']) && $_GET['movement_type'] == 'incoming' ? 'selected' : '' ?>>Надходження</option>
                            <option value="outgoing" <?= isset($_GET['movement_type']) && $_GET['movement_type'] == 'outgoing' ? 'selected' : '' ?>>Витрата</option>
                        </select>
                    </div>
                    
                    <!-- Период -->
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
    
    <!-- Список движений -->
    <div class="col-md-9">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Історія руху товарів</h6>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-export me-1"></i> Експорт
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= base_url('admin/warehouse/export_movements?format=csv') . '&' . http_build_query($_GET) ?>">CSV</a></li>
                        <li><a class="dropdown-item" href="<?= base_url('admin/warehouse/export_movements?format=excel') . '&' . http_build_query($_GET) ?>">Excel</a></li>
                        <li><a class="dropdown-item" href="<?= base_url('admin/warehouse/export_movements?format=pdf') . '&' . http_build_query($_GET) ?>">PDF</a></li>
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
                        <table class="table table-hover movements-table">
                            <thead>
                                <tr>
                                    <th>Дата</th>
                                    <th>Продукт</th>
                                    <th>Склад</th>
                                    <th>Тип руху</th>
                                    <th>Кількість</th>
                                    <th>Створено</th>
                                    <th>Примітки</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($movements as $movement): ?>
                                    <tr>
                                        <td><?= date('d.m.Y H:i', strtotime($movement['created_at'])) ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?= $movement['image'] ? upload_url($movement['image']) : asset_url('images/no-image.jpg') ?>" alt="<?= $movement['product_name'] ?>" class="me-2">
                                                <?= $movement['product_name'] ?>
                                            </div>
                                        </td>
                                        <td><?= $movement['warehouse_name'] ?></td>
                                        <td>
                                            <?php if ($movement['movement_type'] == 'incoming'): ?>
                                                <span class="badge bg-success">Надходження</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Витрата</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= abs($movement['quantity']) ?> шт.</td>
                                        <td><?= $movement['first_name'] . ' ' . $movement['last_name'] ?></td>
                                        <td><?= $movement['notes'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Пагинация -->
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
    </div>
</div>