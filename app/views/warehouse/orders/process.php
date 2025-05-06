<?php
// app/views/warehouse/orders/process.php - Сторінка обробки замовлення менеджером складу
$title = 'Обробка замовлення #' . $order['order_number'];

// Підключення додаткових CSS стилів
$extra_css = '
<style>
    .product-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 4px;
    }
    
    .item-ready {
        background-color: #e8f5e9;
    }
    
    .processing-steps {
        counter-reset: step-counter;
        list-style-type: none;
        padding-left: 0;
    }
    
    .processing-steps li {
        position: relative;
        padding-left: 2.5rem;
        margin-bottom: 1rem;
        counter-increment: step-counter;
    }
    
    .processing-steps li:before {
        content: counter(step-counter);
        position: absolute;
        left: 0;
        top: 0;
        width: 1.8rem;
        height: 1.8rem;
        border-radius: 50%;
        background-color: #4e73df;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
</style>';

// Підключення додаткових JS скриптів
$extra_js = '
<script>
    $(document).ready(function() {
        // Позначення товарів як зібраних
        $(".item-checkbox").on("change", function() {
            const row = $(this).closest("tr");
            if ($(this).is(":checked")) {
                row.addClass("item-ready");
            } else {
                row.removeClass("item-ready");
            }
            
            // Перевірка, чи всі товари зібрані
            const total = $(".item-checkbox").length;
            const checked = $(".item-checkbox:checked").length;
            
            if (checked === total) {
                $("#completeProcessingBtn").removeClass("disabled");
            } else {
                $("#completeProcessingBtn").addClass("disabled");
            }
            
            // Оновлення прогресу
            const percentage = Math.round((checked / total) * 100);
            $("#processingProgress").css("width", percentage + "%").text(percentage + "%");
        });
        
        // Вибрати всі товари
        $("#selectAllItems").on("change", function() {
            const isChecked = $(this).is(":checked");
            $(".item-checkbox").prop("checked", isChecked).trigger("change");
        });
    });
</script>';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('warehouse') ?>">Панель складу</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('orders') ?>">Замовлення</a></li>
                <li class="breadcrumb-item active">Обробка <?= $order['order_number'] ?></li>
            </ol>
        </nav>
    </div>
    <div class="col-md-6 text-end">
        <a href="<?= base_url('orders/view/' . $order['id']) ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Повернутися до замовлення
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="m-0 font-weight-bold">Список товарів для збирання</h5>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="selectAllItems">
                    <label class="form-check-label text-white" for="selectAllItems">
                        Вибрати всі
                    </label>
                </div>
            </div>
            <div class="card-body">
                <div class="progress mb-4">
                    <div id="processingProgress" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                </div>
                
                <form action="<?= base_url('orders/complete_processing/' . $order['id']) ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 40px;"></th>
                                    <th style="width: 60px;"></th>
                                    <th>Найменування</th>
                                    <th class="text-center">Кількість</th>
                                    <th>Місце на складі</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orderItems as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input item-checkbox" type="checkbox" name="processed_items[]" value="<?= $item['id'] ?>">
                                            </div>
                                        </td>
                                        <td>
                                            <img src="<?= $item['image'] ? upload_url($item['image']) : asset_url('images/no-image.jpg') ?>" 
                                                 alt="<?= $item['product_name'] ?>" class="product-image">
                                        </td>
                                        <td><?= $item['product_name'] ?></td>
                                        <td class="text-center"><?= $item['quantity'] ?></td>
                                        <td>
                                            <?php 
                                            // В реальному проекті тут буде інформація про розташування товару на складі
                                            echo 'Стелаж ' . chr(65 + ($item['product_id'] % 6)) . ', Полиця ' . (($item['product_id'] % 10) + 1);
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-grid gap-2 mt-3">
                        <button type="submit" id="completeProcessingBtn" class="btn btn-success disabled">
                            <i class="fas fa-check-circle me-1"></i> Завершити обробку замовлення
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Інструкції щодо обробки замовлення -->
        <div class="card shadow mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="m-0 font-weight-bold">Інструкції з обробки</h5>
            </div>
            <div class="card-body">
                <ol class="processing-steps">
                    <li>Перевірте наявність всіх товарів в замовленні на складі</li>
                    <li>Зберіть товари зі складу відповідно до списку</li>
                    <li>Відмітьте кожен товар як зібраний, коли він готовий</li>
                    <li>Підготуйте пакування відповідно до кількості та типу товарів</li>
                    <li>Після збору всіх товарів, натисніть кнопку "Завершити обробку замовлення"</li>
                </ol>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i> 
                    <strong>Увага!</strong> Якщо якийсь товар відсутній на складі, зверніться до менеджера з продажу для узгодження заміни.
                </div>
            </div>
        </div>
        
        <!-- Інформація про замовлення -->
        <div class="card shadow mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="m-0 font-weight-bold">Деталі замовлення</h5>
            </div>
            <div class="card-body">
                <p><strong>Номер замовлення:</strong> <?= $order['order_number'] ?></p>
                <p><strong>Дата замовлення:</strong> <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></p>
                <p><strong>Клієнт:</strong> <?= $order['first_name'] . ' ' . $order['last_name'] ?></p>
                <p><strong>Телефон:</strong> <?= $order['phone'] ?></p>
                <p><strong>Адреса доставки:</strong><br> <?= nl2br($order['shipping_address']) ?></p>
                
                <?php if (!empty($order['notes'])): ?>
                    <p><strong>Примітки до замовлення:</strong><br> <?= nl2br($order['notes']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>