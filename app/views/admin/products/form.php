<?php
// app/views/admin/products/form.php - Обновленная форма с объемами тары
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
    
    .container-item {
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        padding: 15px;
        margin-bottom: 15px;
        border: 1px solid #dee2e6;
        position: relative;
    }
    
    .container-item.template {
        display: none;
    }
    
    .remove-container {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    
    .remove-container:hover {
        background: #c82333;
    }
    
    .volume-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        background: #007bff;
        color: white;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: bold;
    }
</style>';

// Дополнительные JS скрипты
$extra_js = '
<script>
    $(document).ready(function() {
        let containerCounter = $(".container-item:not(.template)").length;
        
        // Предпросмотр изображения
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
        
        // Добавление нового объема тары
        $("#addContainer").on("click", function() {
            const template = $(".container-item.template").clone();
            template.removeClass("template");
            template.find("input, select").each(function() {
                const name = $(this).attr("name");
                if (name) {
                    $(this).attr("name", name.replace("[]", "[" + containerCounter + "]"));
                }
            });
            
            $("#containersContainer").append(template);
            containerCounter++;
            updateContainerNumbers();
        });
        
        // Удаление объема тары
        $(document).on("click", ".remove-container", function() {
            if ($(".container-item:not(.template)").length > 1) {
                $(this).closest(".container-item").remove();
                updateContainerNumbers();
            } else {
                alert("Необходимо оставить хотя бы один объем тары");
            }
        });
        
        // Обновление нумерации объемов
        function updateContainerNumbers() {
            $(".container-item:not(.template)").each(function(index) {
                $(this).find(".volume-badge").text((index + 1) + " тара");
                $(this).find("input, select").each(function() {
                    const name = $(this).attr("name");
                    if (name && name.includes("[")) {
                        const baseName = name.substring(0, name.indexOf("["));
                        const fieldName = name.substring(name.lastIndexOf("]") + 1);
                        $(this).attr("name", baseName + "[" + index + "]" + fieldName);
                    }
                });
            });
        }
        
        // Валидация формы
        $("#productForm").on("submit", function(e) {
            let hasError = false;
            
            // Проверяем, что все объемы уникальны
            const volumes = [];
            $(".container-item:not(.template) .volume-input").each(function() {
                const volume = parseFloat($(this).val());
                if (volumes.includes(volume)) {
                    alert("Об\'єми тари повинні бути унікальними");
                    hasError = true;
                    return false;
                }
                volumes.push(volume);
            });
            
            if (hasError) {
                e.preventDefault();
            }
        });
        
        // Автоматическое обновление цены за литр
        $(document).on("input", ".container-item input[name*=\"[price]\"], .container-item select[name*=\"[volume]\"]", function() {
            const container = $(this).closest(".container-item");
            const price = parseFloat(container.find("input[name*=\"[price]\"]").val()) || 0;
            const volume = parseFloat(container.find("select[name*=\"[volume]\"]").val()) || 1;
            const pricePerLiter = (price / volume).toFixed(2);
            
            container.find(".price-per-liter").text(pricePerLiter + " грн/л");
        });
        
        // Инициализация цены за литр для существующих контейнеров
        $(".container-item:not(.template)").each(function() {
            const container = $(this);
            const price = parseFloat(container.find("input[name*=\"[price]\"]").val()) || 0;
            const volume = parseFloat(container.find("select[name*=\"[volume]\"]").val()) || 1;
            const pricePerLiter = (price / volume).toFixed(2);
            
            container.find(".price-per-liter").text(pricePerLiter + " грн/л");
        });
        
        // Добавляем первый контейнер, если их нет
        if ($(".container-item:not(.template)").length === 0) {
            $("#addContainer").click();
        }
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
                <form id="productForm" action="<?= $actionUrl ?>" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <?= csrf_field() ?>
                    
                    <div class="row mb-4">
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
                                <textarea class="form-control <?= has_error('description') ? 'is-invalid' : '' ?>" id="description" name="description" rows="4"><?= old('description', $product['description'] ?? '') ?></textarea>
                                <?php if (has_error('description')): ?>
                                    <div class="invalid-feedback"><?= get_error('description') ?></div>
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
                    
                    <!-- Объемы тары -->
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-wine-bottle me-2"></i>
                                Об\'єми тари
                            </h5>
                            <button type="button" id="addContainer" class="btn btn-light btn-sm">
                                <i class="fas fa-plus me-1"></i> Додати об\'єм
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="containersContainer">
                                <!-- Шаблон для нового объема -->
                                <div class="container-item template">
                                    <div class="volume-badge">1 тара</div>
                                    <button type="button" class="remove-container">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label">Об\'єм (л) <span class="text-danger">*</span></label>
                                            <select class="form-select volume-input" name="containers[][volume]" required>
                                                <option value="0.25">0.25 л</option>
                                                <option value="0.5">0.5 л</option>
                                                <option value="1" selected>1 л</option>
                                                <option value="5">5 л</option>
                                                <option value="10">10 л</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Ціна (грн) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="containers[][price]" step="0.01" min="0" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Кількість на складі</label>
                                            <input type="number" class="form-control" name="containers[][stock_quantity]" min="0" value="0">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Ціна за літр</label>
                                            <div class="form-control-plaintext price-per-liter">0.00 грн/л</div>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" name="containers[][is_active]" value="1" checked>
                                                <label class="form-check-label">Активний</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Существующие объемы -->
                                <?php if (isset($product) && !empty($containers)): ?>
                                    <?php foreach ($containers as $index => $container): ?>
                                        <div class="container-item">
                                            <div class="volume-badge"><?= $index + 1 ?> тара</div>
                                            <button type="button" class="remove-container">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            
                                            <input type="hidden" name="containers[<?= $index ?>][id]" value="<?= $container['id'] ?>">
                                            
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <label class="form-label">Об\'єм (л) <span class="text-danger">*</span></label>
                                                    <select class="form-select volume-input" name="containers[<?= $index ?>][volume]" required>
                                                        <option value="0.25" <?= $container['volume'] == 0.25 ? 'selected' : '' ?>>0.25 л</option>
                                                        <option value="0.5" <?= $container['volume'] == 0.5 ? 'selected' : '' ?>>0.5 л</option>
                                                        <option value="1" <?= $container['volume'] == 1 ? 'selected' : '' ?>>1 л</option>
                                                        <option value="5" <?= $container['volume'] == 5 ? 'selected' : '' ?>>5 л</option>
                                                        <option value="10" <?= $container['volume'] == 10 ? 'selected' : '' ?>>10 л</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Ціна (грн) <span class="text-danger">*</span></label>
                                                    <input type="number" class="form-control" name="containers[<?= $index ?>][price]" step="0.01" min="0" value="<?= $container['price'] ?>" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Кількість на складі</label>
                                                    <input type="number" class="form-control" name="containers[<?= $index ?>][stock_quantity]" min="0" value="<?= $container['stock_quantity'] ?>">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Ціна за літр</label>
                                                    <div class="form-control-plaintext price-per-liter">0.00 грн/л</div>
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" name="containers[<?= $index ?>][is_active]" value="1" <?= $container['is_active'] ? 'checked' : '' ?>>
                                                        <label class="form-check-label">Активний</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Увага!</strong> Для кожного продукту потрібно вказати хоча б один об\'єм тари. 
                                Ціна за літр розраховується автоматично для порівняння різних об\'ємів.
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