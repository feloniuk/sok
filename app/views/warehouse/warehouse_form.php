<?php
// app/views/warehouse/warehouse_form.php - Форма для створення/редагування складу
$title = isset($warehouse) ? 'Редагування складу: ' . $warehouse['name'] : 'Створення нового складу';
$actionUrl = isset($warehouse) ? base_url('warehouse/update_warehouse/' . $warehouse['id']) : base_url('warehouse/store_warehouse');

// Підключення додаткових CSS
$extra_css = '
<style>
    .form-card {
        border: none;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
</style>';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card form-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas <?= isset($warehouse) ? 'fa-edit' : 'fa-plus-circle' ?> me-2"></i>
                    <?= $title ?>
                </h5>
            </div>
            
            <div class="card-body">
                <form action="<?= $actionUrl ?>" method="POST" class="needs-validation" novalidate>
                    <?= csrf_field() ?>
                    
                    <!-- Назва складу -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Назва складу <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= has_error('name') ? 'is-invalid' : '' ?>" id="name" name="name" value="<?= old('name', $warehouse['name'] ?? '') ?>" required>
                        <?php if (has_error('name')): ?>
                            <div class="invalid-feedback"><?= get_error('name') ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Адреса складу -->
                    <div class="mb-3">
                        <label for="address" class="form-label">Адреса складу <span class="text-danger">*</span></label>
                        <textarea class="form-control <?= has_error('address') ? 'is-invalid' : '' ?>" id="address" name="address" rows="3" required><?= old('address', $warehouse['address'] ?? '') ?></textarea>
                        <?php if (has_error('address')): ?>
                            <div class="invalid-feedback"><?= get_error('address') ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Менеджер складу -->
                    <div class="mb-3">
                        <label for="manager_id" class="form-label">Менеджер складу</label>
                        <select class="form-select <?= has_error('manager_id') ? 'is-invalid' : '' ?>" id="manager_id" name="manager_id">
                            <option value="">Виберіть менеджера</option>
                            <?php foreach ($managers ?? [] as $manager): ?>
                                <option value="<?= $manager['id'] ?>" <?= old('manager_id', $warehouse['manager_id'] ?? '') == $manager['id'] ? 'selected' : '' ?>>
                                    <?= $manager['first_name'] . ' ' . $manager['last_name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (has_error('manager_id')): ?>
                            <div class="invalid-feedback"><?= get_error('manager_id') ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="<?= base_url('warehouse/warehouses') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Назад до списку
                        </a>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas <?= isset($warehouse) ? 'fa-save' : 'fa-plus-circle' ?> me-1"></i>
                            <?= isset($warehouse) ? 'Зберегти зміни' : 'Створити склад' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>