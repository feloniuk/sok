<?php
// app/views/admin/categories/form.php
$title = isset($category) ? 'Редагування категорії: ' . $category['name'] : 'Створення нової категорії';
$actionUrl = isset($category) ? base_url('categories/update/' . $category['id']) : base_url('categories/store');

// Додаткові CSS стилі
$extra_css = '
<style>
    .form-card {
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        border: none;
    }
    
    .category-image-preview {
        max-height: 200px;
        border-radius: 0.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
</style>';

// Додаткові JS скрипти
$extra_js = '
<script>
    $(document).ready(function() {
        // Попередній перегляд зображення перед завантаженням
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
    <div class="col-md-8">
        <div class="card form-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas <?= isset($category) ? 'fa-edit' : 'fa-plus-circle' ?> me-2"></i>
                    <?= $title ?>
                </h5>
            </div>
            
            <div class="card-body">
                <form action="<?= $actionUrl ?>" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <?= csrf_field() ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <!-- Назва -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Назва категорії <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= has_error('name') ? 'is-invalid' : '' ?>" id="name" name="name" value="<?= old('name', $category['name'] ?? '') ?>" required>
                                <?php if (has_error('name')): ?>
                                    <div class="invalid-feedback"><?= get_error('name') ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Опис -->
                            <div class="mb-3">
                                <label for="description" class="form-label">Опис категорії</label>
                                <textarea class="form-control <?= has_error('description') ? 'is-invalid' : '' ?>" id="description" name="description" rows="5"><?= old('description', $category['description'] ?? '') ?></textarea>
                                <?php if (has_error('description')): ?>
                                    <div class="invalid-feedback"><?= get_error('description') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <!-- Зображення -->
                            <div class="mb-3">
                                <label for="image" class="form-label">Зображення</label>
                                <input type="file" class="form-control <?= has_error('image') ? 'is-invalid' : '' ?>" id="image" name="image" accept="image/*">
                                <?php if (has_error('image')): ?>
                                    <div class="invalid-feedback"><?= get_error('image') ?></div>
                                <?php endif; ?>
                                <div class="form-text">Рекомендований розмір: 800x600 пікселів. Максимальний розмір файлу: 5MB.</div>
                            </div>
                            
                            <!-- Попередній перегляд зображення -->
                            <div class="text-center mt-3">
                                <?php if (isset($category) && $category['image']): ?>
                                    <div id="currentImageContainer">
                                        <img src="<?= upload_url($category['image']) ?>" alt="<?= $category['name'] ?>" class="category-image-preview mb-2">
                                        <p class="text-muted small">Поточне зображення</p>
                                    </div>
                                <?php endif; ?>
                                <img id="imagePreview" src="#" alt="Попередній перегляд" class="category-image-preview" style="display: none;">
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="<?= base_url('categories') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Назад до списку
                        </a>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas <?= isset($category) ? 'fa-save' : 'fa-plus-circle' ?> me-1"></i>
                            <?= isset($category) ? 'Зберегти зміни' : 'Створити категорію' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>