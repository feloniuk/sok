<?php
// app/views/warehouse/orders/ship.php - Сторінка відвантаження замовлення менеджером складу
$title = 'Відвантаження замовлення #' . $order['order_number'];

// Підключення додаткових CSS стилів
$extra_css = '
<style>
    .product-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 4px;
    }
    
    .shipping-steps {
        counter-reset: step-counter;
        list-style-type: none;
        padding-left: 0;
    }
    
    .shipping-steps li {
        position: relative;
        padding-left: 2.5rem;
        margin-bottom: 1rem;
        counter-increment: step-counter;
    }
    
    .shipping-steps li:before {
        content: counter(step-counter);
        position: absolute;
        left: 0;
        top: 0;
        width: 1.8rem;
        height: 1.8rem;
        border-radius: 50%;
        background-color: #1cc88a;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
    
    .shipping-label {
        border: 2px dashed #ddd;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .shipping-label h5 {
        border-bottom: 1px solid #eee;
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
    }
</style>';

// Підключення додаткових JS скриптів
$extra_js = '
<script>
    $(document).ready(function() {
        // Функція друку транспортної накладної
        $("#printShippingLabel").on("click", function(e) {
            e.preventDefault();
            const labelContent = document.getElementById("shippingLabel");
            const originalContent = document.body.innerHTML;
            
            document.body.innerHTML = labelContent.innerHTML;
            window.print();
            document.body.innerHTML = originalContent;
            
            // Перезавантаження скриптів
            location.reload();
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
                <li class="breadcrumb-item active">Відвантаження <?= $order['order_number'] ?></li>
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
            <div class="card-header bg-primary text-white">
                <h5 class="m-0 font-weight-bold">Список товарів для відвантаження</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 60px;"></th>
                                <th>Найменування</th>
                                <th class="text-center">Кількість</th>
                                <th>Вага (кг)</th>
                                <th>Розмір пакування</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalWeight = 0;
                            $totalVolume = 0;
                            
                            foreach ($orderItems as $item): 
                                // Приблизна вага товару (для демонстрації)
                                $weight = round(0.5 + ($item['price'] / 100), 2);
                                $itemTotalWeight = $weight * $item['quantity'];
                                $totalWeight += $itemTotalWeight;
                                
                                // Приблизний розмір пакування
                                $size = ($item['price'] > 100) ? 'Велике' : (($item['price'] > 50) ? 'Середнє' : 'Мале');
                                $volume = ($size == 'Велике') ? 0.5 : (($size == 'Середнє') ? 0.3 : 0.1);
                                $totalVolume += $volume * $item['quantity'];
                            ?>
                                <tr>
                                    <td>
                                        <img src="<?= $item['image'] ? upload_url($item['image']) : asset_url('images/no-image.jpg') ?>" 
                                             alt="<?= $item['product_name'] ?>" class="product-image">
                                    </td>
                                    <td><?= $item['product_name'] ?></td>
                                    <td class="text-center"><?= $item['quantity'] ?></td>
                                    <td><?= $itemTotalWeight ?> кг</td>
                                    <td><?= $size ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-primary">
                                <td colspan="3" class="text-end"><strong>Загальна вага:</strong></td>
                                <td colspan="2"><strong><?= $totalWeight ?> кг</strong></td>
                            </tr>
                            <tr class="table-primary">
                                <td colspan="3" class="text-end"><strong>Приблизний об'єм:</strong></td>
                                <td colspan="2"><strong><?= $totalVolume ?> м³</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <form action="<?= base_url('orders/update_status/' . $order['id']) ?>" method="POST" class="mt-3">
                    <?= csrf_field() ?>
                    <input type="hidden" name="status" value="shipped">
                    
                    <div class="mb-3">
                        <label for="tracking_number" class="form-label">Номер відстеження</label>
                        <input type="text" class="form-control" id="tracking_number" name="tracking_number" 
                               placeholder="Введіть номер відстеження посилки">
                    </div>
                    
                    <div class="mb-3">
                        <label for="shipping_notes" class="form-label">Примітки до відвантаження</label>
                        <textarea class="form-control" id="shipping_notes" name="shipping_notes" rows="3" 
                                  placeholder="Додаткова інформація для кур'єра або одержувача"></textarea>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-truck me-1"></i> Підтвердити відвантаження
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Інструкції щодо відвантаження -->
        <div class="card shadow mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="m-0 font-weight-bold">Інструкції з відвантаження</h5>
            </div>
            <div class="card-body">
                <ol class="shipping-steps">
                    <li>Переконайтесь, що всі товари правильно упаковані</li>
                    <li>Виберіть відповідний тип упаковки відповідно до ваги і розміру</li>
                    <li>Прикріпіть транспортну накладну до упаковки</li>
                    <li>Введіть номер відстеження в форму</li>
                    <li>Підтвердіть відвантаження для зміни статусу замовлення</li>
                </ol>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i> 
                    <strong>Увага!</strong> Переконайтесь, що вага та об'єм відповідають вказаним у транспортній накладній.
                </div>
                
                <div class="d-grid gap-2">
                    <button id="printShippingLabel" class="btn btn-info">
                        <i class="fas fa-print me-1"></i> Друк транспортної накладної
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Транспортна накладна -->
        <div id="shippingLabel" class="shipping-label bg-white">
            <div class="text-center mb-3">
                <h5>ТРАНСПОРТНА НАКЛАДНА</h5>
                <h6>Замовлення #<?= $order['order_number'] ?></h6>
            </div>
            
            <div class="row mb-3">
                <div class="col-6">
                    <p class="mb-1"><strong>Відправник:</strong></p>
                    <p class="mb-1">ТОВ "Соковий завод"</p>
                    <p class="mb-1">м. Київ, вул. Фруктова, 10</p>
                    <p class="mb-0">Тел.: +380 (44) 123-45-67</p>
                </div>
                <div class="col-6">
                    <p class="mb-1"><strong>Отримувач:</strong></p>
                    <p class="mb-1"><?= $order['first_name'] . ' ' . $order['last_name'] ?></p>
                    <p class="mb-1"><?= nl2br($order['shipping_address']) ?></p>
                    <p class="mb-0">Тел.: <?= $order['phone'] ?></p>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-6">
                    <p class="mb-1"><strong>Вага:</strong> <?= $totalWeight ?> кг</p>
                    <p class="mb-0"><strong>Об'єм:</strong> <?= $totalVolume ?> м³</p>
                </div>
                <div class="col-6">
                    <p class="mb-1"><strong>Дата:</strong> <?= date('d.m.Y') ?></p>
                    <p class="mb-0"><strong>Платник:</strong> <?= ($order['payment_method'] == 'cash_on_delivery') ? 'Отримувач' : 'Відправник' ?></p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <p class="mb-1"><strong>Вміст:</strong></p>
                    <p class="mb-1">
                        <?php 
                        $itemsList = [];
                        foreach ($orderItems as $item) {
                            $itemsList[] = $item['product_name'] . ' (' . $item['quantity'] . ' шт.)';
                        }
                        echo implode(', ', $itemsList);
                        ?>
                    </p>
                </div>
            </div>
            
            <div class="mt-4 d-flex justify-content-between">
                <div>
                    <p class="mb-1"><strong>Підпис відправника:</strong></p>
                    <p>____________________</p>
                </div>
                <div>
                    <p class="mb-1"><strong>Підпис отримувача:</strong></p>
                    <p>____________________</p>
                </div>
            </div>
        </div>
    </div>
</div>