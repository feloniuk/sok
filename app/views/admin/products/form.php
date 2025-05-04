<?php
// app/views/admin/products/form.php
$title = isset($product) ? 'Редагування продукту: ' . $product['name'] : 'Створення нового продукту';
$actionUrl = isset($product) ? base_url('admin/products/update/' . $product['id']) : base_url('admin/products/store');

// Дополнительные CSS стили
$extra_css = '
<style>
    .form-card {
        border: none;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .product-image-preview {
        max-height: 200px;
        border-radius: 0.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
</style>';

// Дополнительные скрипты
$extra_js = '
<script>
    $(document).ready(function() {
        // Предпросмотр изображения перед загрузкой
        $("#image").on("change", function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $("#imagePreview").attr("src", e.target.result).show();
                    $("#currentImageContainer").hide();
                };
                reader.readAsDataURL(file);
            }
        });
    });
</script>';
?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card form-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas <?= isset($product) ? 'fa-edit' : 'fa-plus-circle' ?> me-2"></i>
                    <?= $title ?>
                </h5>
            </div>
            
            <div class="card-body">
                <form action="<?= $actionUrl ?>" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <?= csrf_field() ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <!-- Название -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Назва продукту <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= has_error('name') ? 'is-invalid' : '' ?>" id="name" name="name" value="<?= old('name', $product['name'] ?? '') ?>" required>
                                <?php if (has_error('name')): ?>
                                    <div class="invalid-feedback"><?= get_error('name') ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Категория -->
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Категорія</label>
                                <select class="form-select <?= has_error('category_id') ? 'is-invalid' : '' ?>" id="category_id" name="category_id">
                                    <option value="">Виберіть категорію</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>" <?= old('category_id', $product['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                                            <?= $category['name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (has_error('category_id')): ?>
                                    <div class="invalid-feedback"><?= get_error('category_id') ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Описание -->
                            <div class="mb-3">
                                <label for="description" class="form-label">Опис продукту</label>
                                <textarea class="form-control <?= has_error('description') ? 'is-invalid' : '' ?>" id="description" name="description" rows="5"><?= old('description', $product['description'] ?? '') ?></textarea>
                                <?php if (has_error('description')): ?>
                                    <div class="invalid-feedback"><?= get_error('description') ?></div>
                                <?php endif; ?>
                            </div>
                        </div><!-- Цена -->
                            <div class="mb-3">
                                <label for="price" class="form-label">Ціна <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control <?= has_error('price') ? 'is-invalid' : '' ?>" id="price" name="price" value="<?= old('price', $product['price'] ?? '') ?>" step="0.01" min="0" required>
                                    <span class="input-group-text">грн.</span>
                                    <?php if (has_error('price')): ?>
                                        <div class="invalid-feedback"><?= get_error('price') ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Количество на складе -->
                            <div class="mb-3">
                                <label for="stock_quantity" class="form-label">Кількість на складі</label>
                                <input type="number" class="form-control <?= has_error('stock_quantity') ? 'is-invalid' : '' ?>" id="stock_quantity" name="stock_quantity" value="<?= old('stock_quantity', $product['stock_quantity'] ?? 0) ?>" min="0">
                                <?php if (has_error('stock_quantity')): ?>
                                    <div class="invalid-feedback"><?= get_error('stock_quantity') ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Флажки -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" <?= old('is_featured', $product['is_featured'] ?? 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="is_featured">
                                            Рекомендований продукт
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= old('is_active', $product['is_active'] ?? 1) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="is_active">
                                            Активний (доступний для покупки)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <!-- Изображение -->
                            <div class="mb-3">
                                <label for="image" class="form-label">Зображення</label>
                                <input type="file" class="form-control <?= has_error('image') ? 'is-invalid' : '' ?>" id="image" name="image" accept="image/*">
                                <?php if (has_error('image')): ?>
                                    <div class="invalid-feedback"><?= get_error('image') ?></div>
                                <?php endif; ?>
                                <div class="form-text">Рекомендований розмір: 800x600 пікселів. Максимальний розмір файлу: 5MB.</div>
                            </div>
                            
                            <!-- Предпросмотр изображения -->
                            <div class="text-center mt-3">
                                <?php if (isset($product) && $product['image']): ?>
                                    <div id="currentImageContainer">
                                        <img src="<?= upload_url($product['image']) ?>" alt="<?= $product['name'] ?>" class="product-image-preview mb-2">
                                        <p class="text-muted small">Поточне зображення</p>
                                    </div>
                                <?php endif; ?>
                                <img id="imagePreview" src="#" alt="Попередній перегляд" class="product-image-preview" style="display: none;">
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="<?= base_url('admin/products') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Назад до списку
                        </a>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas <?= isset($product) ? 'fa-save' : 'fa-plus-circle' ?> me-1"></i>
                            <?= isset($product) ? 'Зберегти зміни' : 'Створити продукт' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>