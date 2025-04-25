<?php
// app/views/products/view.php - Деталі продукту
$title = $product['name'] ?? 'Деталі продукту';

// Підключення додаткових CSS
$extra_css = '
<style>
    .product-image {
        max-height: 400px;
        width: 100%;
        object-fit: contain;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .product-price {
        font-size: 2rem;
        font-weight: bold;
        color: #007bff;
    }
    
    .product-old-price {
        text-decoration: line-through;
        color: #6c757d;
        font-size: 1.25rem;
    }
    
    .product-features li {
        margin-bottom: 0.5rem;
    }
    
    .related-product-card {
        transition: all 0.3s ease;
        overflow: hidden;
        height: 100%;
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .related-product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .related-product-card .card-img-top {
        height: 150px;
        object-fit: cover;
    }
</style>';

// Функція для визначення класу бейджа наявності
function getStockBadgeClass($quantity) {
    if ($quantity > 10) {
        return 'success';
    } elseif ($quantity > 0) {
        return 'warning';
    } else {
        return 'danger';
    }
}
?>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="position-relative">
            <?php if ($product['is_featured']): ?>
                <span class="badge bg-warning position-absolute top-0 end-0 m-3">Акція</span>
            <?php endif; ?>
            <img src="<?= $product['image'] ? upload_url($product['image']) : asset_url('images/no-image.jpg') ?>" alt="<?= $product['name'] ?>" class="product-image">
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Головна</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('products') ?>">Продукція</a></li>
                <?php if (isset($product['category_id']) && isset($product['category_name'])): ?>
                    <li class="breadcrumb-item"><a href="<?= base_url('categories/view/' . $product['category_id']) ?>"><?= $product['category_name'] ?></a></li>
                <?php endif; ?>
                <li class="breadcrumb-item active" aria-current="page"><?= $product['name'] ?></li>
            </ol>
        </nav>
        
        <h1 class="mb-3"><?= $product['name'] ?></h1>
        
        <div class="mb-3">
            <span class="badge bg-<?= getStockBadgeClass($product['stock_quantity']) ?> p-2">
                <?php if ($product['stock_quantity'] > 0): ?>
                    <i class="fas fa-check-circle me-1"></i> В наявності (<?= $product['stock_quantity'] ?> шт.)
                <?php else: ?>
                    <i class="fas fa-times-circle me-1"></i> Немає в наявності
                <?php endif; ?>
            </span>
            
            <?php if (isset($product['category_name']) && $product['category_name']): ?>
                <span class="badge bg-primary p-2 ms-2">
                    <i class="fas fa-tag me-1"></i> <?= $product['category_name'] ?>
                </span>
            <?php endif; ?>
        </div>
        
        <div class="mb-4">
            <?php if ($product['is_featured']): ?>
                <span class="product-old-price"><?= number_format($product['price'] * 1.2, 2) ?> грн.</span>
            <?php endif; ?>
            <span class="product-price ms-2"><?= number_format($product['price'], 2) ?> грн.</span>
        </div>
        
        <div class="mb-4">
            <p class="text-muted"><?= nl2br($product['description']) ?></p>
        </div>
        
        <div class="d-grid gap-2 mb-4">
            <?php if (is_logged_in() && has_role('customer') && $product['stock_quantity'] > 0): ?>
                <a href="<?= base_url('orders/create?product_id=' . $product['id']) ?>" class="btn btn-success btn-lg">
                    <i class="fas fa-cart-plus me-2"></i> Додати до замовлення
                </a>
            <?php elseif (!is_logged_in() && $product['stock_quantity'] > 0): ?>
                <a href="<?= base_url('auth/login') ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-sign-in-alt me-2"></i> Увійдіть, щоб замовити
                </a>
            <?php elseif ($product['stock_quantity'] <= 0): ?>
                <button class="btn btn-secondary btn-lg" disabled>
                    <i class="fas fa-exclamation-circle me-2"></i> Немає в наявності
                </button>
            <?php endif; ?>
            
            <?php if (has_role(['admin', 'warehouse_manager'])): ?>
                <div class="btn-group">
                    <a href="<?= base_url('products/edit/' . $product['id']) ?>" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i> Редагувати
                    </a>
                    
                    <?php if (has_role('admin')): ?>
                        <a href="<?= base_url('products/delete/' . $product['id']) ?>" class="btn btn-danger confirm-delete" data-item-name="товар '<?= $product['name'] ?>'">
                            <i class="fas fa-trash me-1"></i> Видалити
                        </a>
                    <?php endif; ?>
                    
                    <a href="<?= base_url('warehouse/add_movement?product_id=' . $product['id']) ?>" class="btn btn-info">
                        <i class="fas fa-boxes me-1"></i> Управління запасами
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Характеристики</h5>
            </div>
            <div class="card-body">
                <ul class="product-features fa-ul">
                    <li>
                        <span class="fa-li"><i class="fas fa-check-circle text-success"></i></span>
                        <strong>Категорія:</strong> <?= $product['category_name'] ?? 'Не вказано' ?>
                    </li>
                    <li>
                        <span class="fa-li"><i class="fas fa-check-circle text-success"></i></span>
                        <strong>Натуральність:</strong> 100% натуральний продукт
                    </li>
                    <li>
                        <span class="fa-li"><i class="fas fa-check-circle text-success"></i></span>
                        <strong>Об'єм:</strong> 1 літр
                    </li>
                    <li>
                        <span class="fa-li"><i class="fas fa-check-circle text-success"></i></span>
                        <strong>Термін придатності:</strong> 30 днів
                    </li>
                    <li>
                        <span class="fa-li"><i class="fas fa-check-circle text-success"></i></span>
                        <strong>Умови зберігання:</strong> в холодному місці при температурі від +2°C до +6°C
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($relatedProducts)): ?>
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="mb-4">Подібні товари</h3>
            
            <div class="row g-4">
                <?php foreach ($relatedProducts as $relatedProduct): ?>
                    <div class="col-md-3">
                        <div class="card related-product-card h-100">
                            <img src="<?= $relatedProduct['image'] ? upload_url($relatedProduct['image']) : asset_url('images/no-image.jpg') ?>" class="card-img-top" alt="<?= $relatedProduct['name'] ?>">
                            
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= $relatedProduct['name'] ?></h5>
                                <p class="card-text text-muted"><?= mb_substr($relatedProduct['description'], 0, 60) ?>...</p>
                                
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-primary"><?= number_format($relatedProduct['price'], 2) ?> грн.</span>
                                        <a href="<?= base_url('products/view/' . $relatedProduct['id']) ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i> Деталі
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>