<?php
// app/views/categories/view.php - Сторінка перегляду категорії
$title = isset($category['name']) ? $category['name'] : 'Категорія не знайдена';

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
    .category-banner {
        position: relative;
        height: 250px;
        background-size: cover;
        background-position: center;
        border-radius: 0.5rem;
        overflow: hidden;
        margin-bottom: 2rem;
    }
    
    .category-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0.7));
        display: flex;
        align-items: flex-end;
        padding: 2rem;
    }
    
    .category-title {
        color: white;
        margin: 0;
    }
    
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

<?php if (!isset($category)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle me-2"></i> Категорія не знайдена
    </div>
    <a href="<?= base_url('categories') ?>" class="btn btn-primary">
        <i class="fas fa-arrow-left me-1"></i> Повернутися до списку категорій
    </a>
<?php else: ?>

    <div class="category-banner" style="background-image: url('<?= $category['image'] ? upload_url($category['image']) : asset_url('images/no-image.jpg') ?>')">
        <div class="category-overlay">
            <div>
                <h1 class="category-title"><?= $category['name'] ?></h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?= base_url() ?>" class="text-white">Головна</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('categories') ?>" class="text-white">Категорії</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page"><?= $category['name'] ?></li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-8">
            <?php if (!empty($category['description'])): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Опис категорії</h5>
                        <p class="card-text"><?= nl2br($category['description']) ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3">Інформація про категорію</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Кількість товарів
                            <span class="badge bg-primary rounded-pill"><?= count($category['products'] ?? []) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Створено
                            <span><?= date('d.m.Y', strtotime($category['created_at'])) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Останнє оновлення
                            <span><?= date('d.m.Y', strtotime($category['updated_at'])) ?></span>
                        </li>
                    </ul>
                </div>
                <?php if (has_role('admin')): ?>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="<?= base_url('categories/edit/' . $category['id']) ?>" class="btn btn-warning">
                                <i class="fas fa-edit me-1"></i> Редагувати
                            </a>
                            <?php if (count($category['products'] ?? []) == 0): ?>
                                <a href="<?= base_url('categories/delete/' . $category['id']) ?>" class="btn btn-danger confirm-delete" data-item-name="категорію '<?= $category['name'] ?>'">
                                    <i class="fas fa-trash me-1"></i> Видалити
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <h2 class="mb-4">Товари в категорії</h2>
    
    <?php if (empty($category['products'])): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i> У цій категорії товари відсутні.
        </div>
        <?php if (has_role(['admin', 'warehouse_manager'])): ?>
            <a href="<?= base_url('products/create') ?>" class="btn btn-success">
                <i class="fas fa-plus-circle me-1"></i> Додати новий товар
            </a>
        <?php endif; ?>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($category['products'] as $product): ?>
                <div class="col-sm-6 col-md-6 col-lg-4">
                    <div class="card product-card h-100">
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
        <?php if ($category['pagination']['total_pages'] > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($category['pagination']['current_page'] > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= buildFilterUrl(['page' => 1]) ?>">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="<?= buildFilterUrl(['page' => $category['pagination']['current_page'] - 1]) ?>">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php
                    $startPage = max(1, $category['pagination']['current_page'] - 2);
                    $endPage = min($category['pagination']['total_pages'], $category['pagination']['current_page'] + 2);
                    
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <li class="page-item <?= $i == $category['pagination']['current_page'] ? 'active' : '' ?>">
                            <a class="page-link" href="<?= buildFilterUrl(['page' => $i]) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($category['pagination']['current_page'] < $category['pagination']['total_pages']): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= buildFilterUrl(['page' => $category['pagination']['current_page'] + 1]) ?>">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="<?= buildFilterUrl(['page' => $category['pagination']['total_pages']]) ?>">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>