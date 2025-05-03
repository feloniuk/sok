<?php
// app/views/admin/categories/index.php - Categories management for admin
$title = 'Управління категоріями';

// Підключення додаткових CSS
$extra_css = '
<style>
    .category-card {
        transition: all 0.3s ease;
        border-radius: 0.5rem;
        overflow: hidden;
        height: 100%;
        border: none;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .category-card .card-img-top {
        height: 180px;
        object-fit: cover;
    }
    
    .category-product-count {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
    }
    
    .add-category-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background-color: #f8f9fa;
        cursor: pointer;
    }
    
    .add-category-icon {
        font-size: 3rem;
        color: #007bff;
        margin-bottom: 1rem;
    }
</style>';

// Підключення додаткових JS
$extra_js = '
<script>
    $(document).ready(function() {
        $(".add-category-card").on("click", function() {
            window.location.href = "' . base_url('categories/create') . '";
        });
    });
</script>';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h3 mb-0 text-gray-800"><?= $title ?></h1>
        <p class="text-muted">Загальна кількість категорій: <?= count($categories) ?></p>
    </div>
    <div class="col-md-4 text-end">
        <a href="<?= base_url('categories/create') ?>" class="btn btn-success">
            <i class="fas fa-plus-circle me-1"></i> Додати нову категорію
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Add Category Card -->
    <div class="col-md-4 mb-4">
        <div class="card category-card add-category-card h-100">
            <div class="card-body text-center py-5">
                <div class="add-category-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <h5 class="card-title">Додати нову категорію</h5>
                <p class="card-text text-muted">Натисніть, щоб створити нову категорію продуктів</p>
            </div>
        </div>
    </div>
    
    <!-- Category Cards -->
    <?php if (empty($categories)): ?>
        <div class="col-md-8">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> Категорії ще не створені. Розпочніть зі створення першої категорії.
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($categories as $category): ?>
            <div class="col-md-4 mb-4">
                <div class="card category-card h-100">
                    <div class="position-relative">
                        <img src="<?= $category['image'] ? upload_url($category['image']) : asset_url('images/no-image.jpg') ?>" class="card-img-top" alt="<?= $category['name'] ?>">
                        <span class="category-product-count">
                            <i class="fas fa-boxes me-1"></i> <?= $category['product_count'] ?? 0 ?> товарів
                        </span>
                    </div>
                    
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= $category['name'] ?></h5>
                        <p class="card-text text-muted flex-grow-1">
                            <?= !empty($category['description']) ? mb_substr($category['description'], 0, 100) . '...' : 'Без опису' ?>
                        </p>
                        
                        <div class="d-flex mt-auto">
                            <a href="<?= base_url('categories/view/' . $category['id']) ?>" class="btn btn-primary flex-grow-1 me-1">
                                <i class="fas fa-eye me-1"></i> Переглянути
                            </a>
                            
                            <div class="btn-group">
                                <a href="<?= base_url('categories/edit/' . $category['id']) ?>" class="btn btn-warning" title="Редагувати">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if (($category['product_count'] ?? 0) == 0): ?>
                                    <a href="<?= base_url('categories/delete/' . $category['id']) ?>" class="btn btn-danger confirm-delete" data-item-name="категорію '<?= $category['name'] ?>'" title="Видалити">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-muted">
                        <small>Створено: <?= date('d.m.Y', strtotime($category['created_at'])) ?></small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>