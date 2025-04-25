<?php
// app/views/products/index.php - Список продуктів
$title = 'Каталог продукції';

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
        transition: all 0.3s ease;
        overflow: hidden;
        height: 100%;
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .product-card .card-img-top {
        height: 200px;
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
</style>';
?>

<div class="row mb-4">
    <!-- Фільтри -->
    <div class="col-md-3">
        <div class="card filter-card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Фільтри</h5>
            </div>
            <div class="card-body">
                <form id="filterForm" action="<?= base_url('products') ?>" method="GET" class="product-filter">
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
                        <div class="row g-2">
                            <div class="col">
                                <input type="number" class="form-control filter-control" name="min_price" placeholder="Від" value="<?= $_GET['min_price'] ?? '' ?>">
                            </div>
                            <div class="col">
                                <input type="number" class="form-control filter-control" name="max_price" placeholder="До" value="<?= $_GET['max_price'] ?? '' ?>">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Сортування -->
                    <div class="mb-3">
                        <label for="sort" class="form-label">Сортування</label>
                        <select class="form-select filter-control" id="sort" name="sort">
                            <option value="name_asc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'name_asc') ? 'selected' : '' ?>>За назвою (А-Я)</option>
                            <option value="name_desc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'name_desc') ? 'selected' : '' ?>>За назвою (Я-А)</option>
                            <option value="price_asc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'price_asc') ? 'selected' : '' ?>>За ціною (зростання)</option>
                            <option value="price_desc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'price_desc') ? 'selected' : '' ?>>За ціною (спадання)</option>
                            <option value="newest" <?= (isset($_GET['sort']) && $_GET['sort'] == 'newest') ? 'selected' : '' ?>>Новинки</option>
                        </select>
                    </div>
                    
                    <!-- Тільки акційні товари -->
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input filter-control" id="is_featured" name="is_featured" value="1" <?= (isset($_GET['is_featured']) && $_GET['is_featured'] == '1') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_featured">Тільки акційні товари</label>
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
        
        <?php if (has_role(['admin', 'warehouse_manager'])): ?>
            <div class="card filter-card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Управління</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= base_url('products/create') ?>" class="btn btn-success">
                            <i class="fas fa-plus-circle me-1"></i> Додати новий товар
                        </a>
                        <?php if (has_role(['admin'])): ?>
                            <a href="<?= base_url('categories') ?>" class="btn btn-primary">
                                <i class="fas fa-list me-1"></i> Управління категоріями
                            </a>
                        <?php endif; ?>
                        <?php if (has_role(['warehouse_manager'])): ?>
                            <a href="<?= base_url('warehouse/inventory') ?>" class="btn btn-info">
                                <i class="fas fa-boxes me-1"></i> Управління запасами
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Список продуктів -->
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Всього знайдено: <?= $pagination['total_items'] ?? 0 ?> товарів</h4>
            <?php if (is_logged_in() && has_role('customer')): ?>
                <a href="<?= base_url('orders/create') ?>" class="btn btn-success">
                    <i class="fas fa-cart-plus me-1"></i> Створити замовлення
                </a>
            <?php endif; ?>
        </div>
        
        <?php if (empty($products)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> На жаль, товари не знайдені. Спробуйте змінити параметри фільтрації.
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($products as $product): ?>
                    <div class="col-sm-6 col-md-6 col-lg-4">
                        <div class="card product-card h-100">
                            <?php if (isset($product['category_name']) && $product['category_name']): ?>
                                <span class="badge bg-primary category-badge"><?= $product['category_name'] ?></span>
                            <?php endif; ?>
                            
                            <?php if ($product['is_featured']): ?>
                                <span class="badge bg-warning badge-featured">Акція</span>
                            <?php endif; ?>
                            
                            <img src="<?= $product['image'] ? upload_url($product['image']) : asset_url('images/no-image.jpg') ?>" class="card-img-top" alt="<?= $product['name'] ?>">
                            
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= $product['name'] ?></h5>
                                <p class="card-text text-muted"><?= mb_substr($product['description'], 0, 100) ?>...</p>
                                
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="product-price fs-5"><?= number_format($product['price'], 2) ?> грн.</span>
                                        <span class="badge bg-<?= $product['stock_quantity'] > 10 ? 'success' : ($product['stock_quantity'] > 0 ? 'warning' : 'danger') ?>">
                                            <?= $product['stock_quantity'] > 0 ? 'В наявності' : 'Немає в наявності' ?>
                                        </span>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <a href="<?= base_url('products/view/' . $product['id']) ?>" class="btn btn-primary flex-grow-1">
                                            <i class="fas fa-eye me-1"></i> Деталі
                                        </a>
                                        
                                        <?php if (is_logged_in()): ?>
                                            <?php if (has_role('customer') && $product['stock_quantity'] > 0): ?>
                                                <a href="<?= base_url('orders/create?product_id=' . $product['id']) ?>" class="btn btn-success">
                                                    <i class="fas fa-cart-plus"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if (has_role(['admin', 'warehouse_manager'])): ?>
                                                <div class="btn-group">
                                                    <a href="<?= base_url('products/edit/' . $product['id']) ?>" class="btn btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <?php if (has_role('admin')): ?>
                                                        <a href="<?= base_url('products/delete/' . $product['id']) ?>" class="btn btn-danger confirm-delete" data-item-name="товар '<?= $product['name'] ?>'">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
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