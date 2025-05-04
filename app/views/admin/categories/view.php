<?php
// app/views/admin/categories/view.php
$title = 'Категорія: ' . $category['name'];

// Додаткові CSS стилі
$extra_css = '
<style>
    .category-header {
        background-position: center;
        background-size: cover;
        background-repeat: no-repeat;
        height: 200px;
        border-radius: 0.5rem;
        position: relative;
        overflow: hidden;
        margin-bottom: 20px;
    }
    
    .category-header-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        color: white;
        text-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
    }
    
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
</style>';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Головна</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('categories') ?>">Категорії</a></li>
                <li class="breadcrumb-item active"><?= $category['name'] ?></li>
            </ol>
        </nav>
    </div>
</div>

<!-- Шапка категорії -->
<div class="category-header" style="background-image: url('<?= $category['image'] ? upload_url($category['image']) : asset_url('images/no-image.jpg') ?>');">
    <div class="category-header-overlay">
        <h1 class="h2 mb-2"><?= $category['name'] ?></h1>
        <p class="mb-0">Кількість продуктів: <?= count($category['products'] ?? []) ?></p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <?php if (!empty($category['description'])): ?>
                    <div class="mb-4">
                        <h5>Опис категорії:</h5>
                        <p><?= $category['description'] ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="d-flex justify-content-between align-items-center">
                    <div class="btn-group">
                        <a href="<?= base_url('categories/edit/' . $category['id']) ?>" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i> Редагувати категорію
                        </a>
                        <?php if (empty($category['products'])): ?>
                            <a href="<?= base_url('categories/delete/' . $category['id']) ?>" class="btn btn-danger confirm-delete" data-item-name="категорію '<?= $category['name'] ?>'" title="Видалити">
                                <i class="fas fa-trash me-1"></i> Видалити категорію
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <a href="<?= base_url('products/create?category_id=' . $category['id']) ?>" class="btn btn-success">
                        <i class="fas fa-plus-circle me-1"></i> Додати продукт в категорію
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Список продуктів категорії -->
<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="h4 mb-3">Продукти в категорії</h2>
        
        <?php if (empty($category['products'])): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> У цій категорії ще немає продуктів.
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($category['products'] as $product): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card product-card h-100">
                            <img src="<?= $product['image'] ? upload_url($product['image']) : asset_url('images/no-image.jpg') ?>" class="card-img-top" alt="<?= $product['name'] ?>">
                            
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= $product['name'] ?></h5>
                                
                                <div class="mb-2">
                                    <?php if ($product['is_active']): ?>
                                        <span class="badge bg-success">Активний</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Неактивний</span>
                                    <?php endif; ?>
                                    
                                    <?php if ($product['is_featured']): ?>
                                        <span class="badge bg-warning">Рекомендований</span>
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
            
            <!-- Пагінація -->
            <?php if ($category['pagination']['total_pages'] > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($category['pagination']['current_page'] > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= base_url('categories/view/' . $category['id'] . '?page=1') ?>">
                                    <i class="fas fa-angle-double-left"></i>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="<?= base_url('categories/view/' . $category['id'] . '?page=' . ($category['pagination']['current_page'] - 1)) ?>">
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
                                <a class="page-link" href="<?= base_url('categories/view/' . $category['id'] . '?page=' . $i) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($category['pagination']['current_page'] < $category['pagination']['total_pages']): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= base_url('categories/view/' . $category['id'] . '?page=' . ($category['pagination']['current_page'] + 1)) ?>">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="<?= base_url('categories/view/' . $category['id'] . '?page=' . $category['pagination']['total_pages']) ?>">
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