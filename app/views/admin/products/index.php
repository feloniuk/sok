<?php
// app/views/admin/products/index.php - Products management for admin
$title = 'Управління продуктами';

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

// Підключення додаткових CSS
$extra_css = '
<style>
    .product-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-radius: 0.5rem;
        overflow: hidden;
        height: 100%;
        border: none;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .product-card .card-img-top {
        height: 180px;
        object-fit: cover;
    }
    
    .product-price {
        font-weight: bold;
        color: #007bff;
    }
    
    .filter-card {
        border: none;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .category-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 1;
    }
    
    .badge-featured {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 1;
    }
    
    .product-grid-view .product-list-item {
        display: none;
    }
    
    .product-list-view .product-grid-item {
        display: none;
    }
    
    .product-list-view .product-list-item {
        display: flex;
    }
    
    .stock-badge-success {
        background-color: #28a745;
    }
    
    .stock-badge-warning {
        background-color: #ffc107;
        color: #212529;
    }
    
    .stock-badge-danger {
        background-color: #dc3545;
    }
</style>';

// Підключення додаткових JS
$extra_js = '
<script>
    $(document).ready(function() {
        // Ініціалізація вибору діапазону цін
        $("#price-range").slider({
            range: true,
            min: 0,
            max: 1000,
            values: [' . ($_GET['min_price'] ?? 0) . ', ' . ($_GET['max_price'] ?? 1000) . '],
            slide: function(event, ui) {
                $("#min_price").val(ui.values[0]);
                $("#max_price").val(ui.values[1]);
                $("#price-range-text").text(ui.values[0] + " грн - " + ui.values[1] + " грн");
            }
        });
        
        // Перемикання режиму перегляду
        $("#viewToggle .btn").on("click", function() {
            const viewMode = $(this).data("view");
            $("#viewToggle .btn").removeClass("active");
            $(this).addClass("active");
            
            if (viewMode === "grid") {
                $("#productsContainer").removeClass("product-list-view").addClass("product-grid-view");
            } else {
                $("#productsContainer").removeClass("product-grid-view").addClass("product-list-view");
            }
        });
        
        // Фільтрація продуктів при зміні параметрів
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
            $("#min_price").val(0);
            $("#max_price").val(1000);
            $("#price-range").slider("values", [0, 1000]);
            $("#price-range-text").text("0 грн - 1000 грн");
            $("#filterForm").submit();
        });
    });
</script>';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h3 mb-0 text-gray-800"><?= $title ?></h1>
        <p class="text-muted">Загальна кількість продуктів: <?= $pagination['total_items'] ?? 0 ?></p>
    </div>
    <div class="col-md-4 text-end">
        <a href="<?= base_url('products/create') ?>" class="btn btn-success">
            <i class="fas fa-plus-circle me-1"></i> Додати новий продукт
        </a>
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
                <form id="filterForm" action="<?= base_url('products') ?>" method="GET">
                    <!-- Пошук за ключовим словом -->
                    <div class="mb-3">
                        <label for="keyword" class="form-label">Пошук</label>
                        <input type="text" class="form-control filter-control" id="keyword" name="keyword" value="<?= $_GET['keyword'] ?? '' ?>" placeholder="Введіть назву продукту...">
                    </div>
                    
                    <!-- Категорія -->
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Категорія</label>
                        <select class="form-select filter-control" id="category_id" name="category_id">
                            <option value="">Всі категорії</option>
                            <?php foreach ($categories ?? [] as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= isset($_GET['category_id']) && $_GET['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                    <?= $category['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Діапазон цін -->
                    <div class="mb-3">
                        <label class="form-label">Ціна</label>
                        <div id="price-range" class="mb-2"></div>
                        <div id="price-range-text" class="text-center mb-2">
                            <?= ($_GET['min_price'] ?? 0) ?> грн - <?= ($_GET['max_price'] ?? 1000) ?> грн
                        </div>
                        <input type="hidden" id="min_price" name="min_price" value="<?= $_GET['min_price'] ?? 0 ?>">
                        <input type="hidden" id="max_price" name="max_price" value="<?= $_GET['max_price'] ?? 1000 ?>">
                    </div>
                    
                    <!-- Статус -->
                    <div class="mb-3">
                        <label for="is_active" class="form-label">Статус</label>
                        <select class="form-select filter-control" id="is_active" name="is_active">
                            <option value="">Всі продукти</option>
                            <option value="1" <?= isset($_GET['is_active']) && $_GET['is_active'] == '1' ? 'selected' : '' ?>>Активні</option>
                            <option value="0" <?= isset($_GET['is_active']) && $_GET['is_active'] == '0' ? 'selected' : '' ?>>Неактивні</option>
                        </select>
                    </div>
                    
                    <!-- Рекомендовані -->
                    <div class="mb-3">
                        <label for="is_featured" class="form-label">Рекомендовані</label>
                        <select class="form-select filter-control" id="is_featured" name="is_featured">
                            <option value="">Всі продукти</option>
                            <option value="1" <?= isset($_GET['is_featured']) && $_GET['is_featured'] == '1' ? 'selected' : '' ?>>Рекомендовані</option>
                            <option value="0" <?= isset($_GET['is_featured']) && $_GET['is_featured'] == '0' ? 'selected' : '' ?>>Не рекомендовані</option>
                        </select>
                    </div>
                    
                    <!-- Сортування -->
                    <div class="mb-3">
                        <label for="sort" class="form-label">Сортування</label>
                        <select class="form-select filter-control" id="sort" name="sort">
                            <option value="id_desc" <?= (!isset($_GET['sort']) || $_GET['sort'] == 'id_desc') ? 'selected' : '' ?>>Нові спочатку</option>
                            <option value="name_asc" <?= isset($_GET['sort']) && $_GET['sort'] == 'name_asc' ? 'selected' : '' ?>>За назвою (А-Я)</option>
                            <option value="name_desc" <?= isset($_GET['sort']) && $_GET['sort'] == 'name_desc' ? 'selected' : '' ?>>За назвою (Я-А)</option>
                            <option value="price_asc" <?= isset($_GET['sort']) && $_GET['sort'] == 'price_asc' ? 'selected' : '' ?>>За ціною (від меншої)</option>
                            <option value="price_desc" <?= isset($_GET['sort']) && $_GET['sort'] == 'price_desc' ? 'selected' : '' ?>>За ціною (від більшої)</option>
                            <option value="stock_asc" <?= isset($_GET['sort']) && $_GET['sort'] == 'stock_asc' ? 'selected' : '' ?>>За кількістю (від меншої)</option>
                            <option value="stock_desc" <?= isset($_GET['sort']) && $_GET['sort'] == 'stock_desc' ? 'selected' : '' ?>>За кількістю (від більшої)</option>
                        </select>
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
    
    <!-- Список продуктів -->
    <div class="col-md-9">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Список продуктів</h6>
                <div class="btn-toolbar">
                    <div class="btn-group btn-group-sm me-2" id="viewToggle">
                        <button type="button" class="btn btn-outline-primary active" data-view="grid">
                            <i class="fas fa-th-large"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary" data-view="list">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= base_url('products/export?format=csv') . '&' . http_build_query($_GET) ?>">Експорт в CSV</a></li>
                        <li><a class="dropdown-item" href="<?= base_url('products/export?format=excel') . '&' . http_build_query($_GET) ?>">Експорт в Excel</a></li>
                        <li><a class="dropdown-item" href="<?= base_url('products/export?format=pdf') . '&' . http_build_query($_GET) ?>">Експорт в PDF</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= base_url('products/import') ?>">Імпорт продуктів</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($products)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Продукти не знайдені. Спробуйте змінити параметри фільтрації.
                    </div>
                <?php else: ?>
                    <div id="productsContainer" class="product-grid-view">
                        <!-- Grid View -->
                        <div class="row g-4 product-grid-item">
                            <?php foreach ($products as $product): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card product-card h-100">
                                        <div class="position-relative">
                                            <?php if (!empty($product['category_id'])): ?>
                                                <span class="badge bg-secondary category-badge">
                                                    <?php
                                                    foreach ($categories as $category) {
                                                        if ($category['id'] == $product['category_id']) {
                                                            echo $category['name'];
                                                            break;
                                                        }
                                                    }
                                                    ?>
                                                </span>
                                            <?php endif; ?>
                                            
                                            <?php if ($product['is_featured']): ?>
                                                <span class="badge bg-warning badge-featured">Рекомендований</span>
                                            <?php endif; ?>
                                            
                                            <img src="<?= $product['image'] ? upload_url($product['image']) : asset_url('images/no-image.jpg') ?>" class="card-img-top" alt="<?= $product['name'] ?>">
                                        </div>
                                        
                                        <div class="card-body d-flex flex-column">
                                            <h5 class="card-title"><?= $product['name'] ?></h5>
                                            
                                            <div class="mb-2">
                                                <?php if ($product['is_active']): ?>
                                                    <span class="badge bg-success">Активний</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Неактивний</span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <p class="card-text text-muted flex-grow-1">
                                                <?= mb_substr($product['description'], 0, 80) ?>...
                                            </p>
                                            
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="product-price fs-5"><?= number_format($product['price'], 2) ?> грн.</span>
                                                <span class="badge bg-<?= $product['stock_quantity'] > 10 ? 'success' : ($product['stock_quantity'] > 0 ? 'warning' : 'danger') ?>">
                                                    <?= $product['stock_quantity'] ?> шт.
                                                </span>
                                            </div>
                                            
                                            <div class="d-flex mt-auto">
                                                <a href="<?= base_url('products/view/' . $product['id']) ?>" class="btn btn-primary flex-grow-1 me-1">
                                                    <i class="fas fa-eye me-1"></i> Деталі
                                                </a>
                                                
                                                <div class="btn-group">
                                                    <a href="<?= base_url('products/edit/' . $product['id']) ?>" class="btn btn-warning" title="Редагувати">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="<?= base_url('products/delete/' . $product['id']) ?>" class="btn btn-danger confirm-delete" data-item-name="продукт '<?= $product['name'] ?>'" title="Видалити">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- List View -->
                        <div class="product-list-item">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width: 60px;"></th>
                                            <th>Назва</th>
                                            <th>Категорія</th>
                                            <th>Ціна</th>
                                            <th>Кількість</th>
                                            <th>Статус</th>
                                            <th>Дії</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($products as $product): ?>
                                            <tr>
                                                <td>
                                                    <img src="<?= $product['image'] ? upload_url($product['image']) : asset_url('images/no-image.jpg') ?>" 
                                                         alt="<?= $product['name'] ?>" 
                                                         class="img-thumbnail" 
                                                         style="width: 50px; height: 50px; object-fit: cover;">
                                                </td>
                                                <td>
                                                    <?= $product['name'] ?>
                                                    <?php if ($product['is_featured']): ?>
                                                        <span class="badge bg-warning ms-1">Рекомендований</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    if (!empty($product['category_id'])) {
                                                        foreach ($categories as $category) {
                                                            if ($category['id'] == $product['category_id']) {
                                                                echo $category['name'];
                                                                break;
                                                            }
                                                        }
                                                    } else {
                                                        echo '<span class="text-muted">Не вказано</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td><?= number_format($product['price'], 2) ?> грн.</td>
                                                <td>
                                                    <span class="badge bg-<?= $product['stock_quantity'] > 10 ? 'success' : ($product['stock_quantity'] > 0 ? 'warning' : 'danger') ?>">
                                                        <?= $product['stock_quantity'] ?> шт.
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($product['is_active']): ?>
                                                        <span class="badge bg-success">Активний</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Неактивний</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="<?= base_url('products/view/' . $product['id']) ?>" class="btn btn-outline-primary" title="Деталі">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="<?= base_url('products/edit/' . $product['id']) ?>" class="btn btn-outline-warning" title="Редагувати">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="<?= base_url('products/delete/' . $product['id']) ?>" class="btn btn-outline-danger confirm-delete" data-item-name="продукт '<?= $product['name'] ?>'" title="Видалити">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
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
    </div>
</div>