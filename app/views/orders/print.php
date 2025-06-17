<?php
// app/views/orders/print.php - Обновленная страница для печати заказа с поддержкой контейнеров
$title = 'Друк замовлення';

// Функции для форматирования
function getStatusName($status) {
    $statusNames = [
        'pending' => 'Очікує обробки',
        'processing' => 'В обробці',
        'shipped' => 'Відправлено',
        'delivered' => 'Доставлено',
        'cancelled' => 'Скасовано'
    ];
    return $statusNames[$status] ?? $status;
}

function getPaymentMethodName($method) {
    $methodNames = [
        'credit_card' => 'Кредитна картка',
        'bank_transfer' => 'Банківський переказ',
        'cash_on_delivery' => 'Оплата при отриманні'
    ];
    return $methodNames[$method] ?? $method;
}

// Дополнительные CSS для страницы печати
$extra_css = '
<style>
    @media print {
        @page {
            size: A4;
            margin: 1cm;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 12pt;
        }
        
        .no-print {
            display: none !important;
        }
        
        .container {
            width: 100%;
            max-width: 100%;
            padding: 0;
            margin: 0;
        }
        
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .text-end {
            text-align: right;
        }
        
        .header {
            margin-bottom: 30px;
        }
        
        .footer {
            margin-top: 50px;
            font-size: 10pt;
        }
        
        .volume-info {
            font-size: 10pt;
            color: #666;
        }
    }
    
    .print-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .print-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 30px;
    }
    
    .order-details {
        display: flex;
        justify-content: space-between;
        margin-bottom: 30px;
    }
    
    .order-details-left, .order-details-right {
        width: 48%;
    }
    
    .print-footer {
        margin-top: 50px;
        padding-top: 20px;
        border-top: 1px solid #ddd;
        text-align: center;
        font-size: 12px;
    }
    
    .print-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
    }
    
    .print-table th, .print-table td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    
    .print-table tr:nth-child(even) {
        background-color: #f8f9fa;
    }
    
    .print-table th {
        background-color: #f1f1f1;
    }
    
    .small-text {
        font-size: 12px;
    }
    
    .signature-area {
        margin-top: 50px;
        display: flex;
        justify-content: space-between;
    }
    
    .signature-box {
        border-top: 1px solid #000;
        padding-top: 5px;
        width: 200px;
        text-align: center;
    }
    
    .container-info {
        background-color: #f8f9fa;
        padding: 4px 8px;
        border-radius: 3px;
        font-size: 0.85rem;
        margin-top: 3px;
        display: inline-block;
    }
    
    .volume-badge {
        background: #007bff;
        color: white;
        padding: 2px 6px;
        border-radius: 8px;
        font-size: 0.7rem;
        font-weight: bold;
        margin-right: 5px;
    }
    
    .price-per-liter {
        color: #6c757d;
        font-size: 0.75rem;
    }
    
    .order-summary-box {
        border: 2px solid #007bff;
        border-radius: 8px;
        padding: 15px;
        background-color: #f8f9ff;
        margin-top: 20px;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        padding-bottom: 5px;
        border-bottom: 1px dashed #ddd;
    }
    
    .summary-row:last-child {
        border-bottom: 2px solid #007bff;
        margin-bottom: 0;
        font-weight: bold;
        font-size: 1.1rem;
    }
</style>';

// Дополнительные JS для страницы печати
$extra_js = '
<script>
    $(document).ready(function() {
        $("#printButton").on("click", function() {
            window.print();
        });
    });
</script>';
?>

<div class="no-print mb-4">
    <div class="d-flex justify-content-between">
        <a href="<?= base_url('orders/view/' . $order['id']) ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Повернутися до замовлення
        </a>
        <button id="printButton" class="btn btn-primary">
            <i class="fas fa-print me-1"></i> Друкувати
        </button>
    </div>
</div>

<div class="print-container">
    <div class="print-header">
        <div>
            <h1 style="margin: 0; font-size: 24px;">Замовлення #<?= $order['order_number'] ?></h1>
            <p>від <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></p>
            <p><strong>Статус:</strong> <?= getStatusName($order['status']) ?></p>
        </div>
        <div style="text-align: right;">
            <img src="<?= asset_url('images/logo.png') ?>" alt="<?= APP_NAME ?>" height="50">
            <p>
                ТОВ "Соки України"<br>
                Код ЄДРПОУ: 12345678<br>
                м. Київ, вул. Соняшникова, 10<br>
                Тел: +380 (44) 123-45-67
            </p>
        </div>
    </div>
    
    <div class="order-details">
        <div class="order-details-left">
            <h3>Інформація про замовлення</h3>
            <p><strong>Спосіб оплати:</strong> <?= getPaymentMethodName($order['payment_method']) ?></p>
            <?php if (!empty($order['notes'])): ?>
                <p><strong>Примітки:</strong> <?= nl2br($order['notes']) ?></p>
            <?php endif; ?>
        </div>
        <div class="order-details-right">
            <h3>Інформація про клієнта</h3>
            <p><strong>Клієнт:</strong> <?= $order['first_name'] . ' ' . $order['last_name'] ?></p>
            <p><strong>Email:</strong> <?= $order['email'] ?></p>
            <?php if (!empty($order['phone'])): ?>
                <p><strong>Телефон:</strong> <?= $order['phone'] ?></p>
            <?php endif; ?>
            <p><strong>Адреса доставки:</strong><br><?= nl2br($order['shipping_address']) ?></p>
        </div>
    </div>
    
    <h3>Товари</h3>
    <table class="print-table">
        <thead>
            <tr>
                <th style="width: 50px;">№</th>
                <th>Найменування</th>
                <th style="width: 80px;" class="text-end">Об'єм</th>
                <th style="width: 80px;" class="text-end">Кількість</th>
                <th style="width: 100px;" class="text-end">Ціна</th>
                <th style="width: 80px;" class="text-end">Ціна/л</th>
                <th style="width: 120px;" class="text-end">Сума</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $totalVolume = 0;
            $totalItems = 0;
            
            foreach ($orderItems as $index => $item): 
                $itemVolume = ($item['volume'] ?? 1) * $item['quantity'];
                $totalVolume += $itemVolume;
                $totalItems += $item['quantity'];
                $pricePerLiter = isset($item['price_per_liter']) ? $item['price_per_liter'] : ($item['price'] / ($item['volume'] ?? 1));
            ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td>
                        <strong><?= $item['product_name'] ?></strong>
                        <?php if (!empty($item['container_id']) && !empty($item['volume'])): ?>
                            <div class="container-info">
                                <span class="volume-badge"><?= $item['volume'] ?> л</span>
                                <span class="price-per-liter"><?= number_format($pricePerLiter, 2) ?> грн/л</span>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="text-end"><?= isset($item['volume']) ? $item['volume'] . ' л' : '1 л' ?></td>
                    <td class="text-end"><?= $item['quantity'] ?> шт</td>
                    <td class="text-end"><?= number_format($item['price'], 2) ?> грн</td>
                    <td class="text-end"><?= number_format($pricePerLiter, 2) ?></td>
                    <td class="text-end"><?= number_format($item['price'] * $item['quantity'], 2) ?> грн</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" style="border-top: 2px solid #333;"></td>
            </tr>
            <tr>
                <td colspan="6" class="text-end"><strong>Загальна кількість товарів:</strong></td>
                <td class="text-end"><strong><?= $totalItems ?> шт</strong></td>
            </tr>
            <tr>
                <td colspan="6" class="text-end"><strong>Загальний об'єм:</strong></td>
                <td class="text-end"><strong><?= number_format($totalVolume, 2) ?> л</strong></td>
            </tr>
            <tr>
                <td colspan="6" class="text-end"><strong>Всього до сплати:</strong></td>
                <td class="text-end"><strong><?= number_format($order['total_amount'], 2) ?> грн</strong></td>
            </tr>
        </tfoot>
    </table>
    
    <!-- Детальная сводка заказа -->
    <div class="order-summary-box">
        <h4 style="margin-bottom: 15px; color: #007bff;">Підсумок замовлення</h4>
        
        <div class="summary-row">
            <span>Унікальних продуктів:</span>
            <span><?= count($orderItems) ?></span>
        </div>
        
        <div class="summary-row">
            <span>Загальна кількість одиниць:</span>
            <span><?= $totalItems ?> шт</span>
        </div>
        
        <div class="summary-row">
            <span>Загальний об'єм:</span>
            <span><?= number_format($totalVolume, 2) ?> л</span>
        </div>
        
        <div class="summary-row">
            <span>Середня ціна за літр:</span>
            <span><?= $totalVolume > 0 ? number_format($order['total_amount'] / $totalVolume, 2) : '0.00' ?> грн/л</span>
        </div>
        
        <div class="summary-row">
            <span>Загальна сума:</span>
            <span><?= number_format($order['total_amount'], 2) ?> грн</span>
        </div>
    </div>
    
    <!-- Область для подписей -->
    <div class="signature-area">
        <div class="signature-box">
            <span class="small-text">Підпис продавця</span>
        </div>
        <div class="signature-box">
            <span class="small-text">Підпис покупця</span>
        </div>
        <div class="signature-box">
            <span class="small-text">Дата отримання</span>
        </div>
    </div>
    
    <!-- Дополнительная информация -->
    <div style="margin-top: 40px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9;">
        <h5>Умови доставки та оплати:</h5>
        <ul style="margin: 10px 0; padding-left: 20px; font-size: 11px;">
            <li>Доставка здійснюється протягом 1-3 робочих днів з моменту підтвердження замовлення</li>
            <li>Вартість доставки розраховується індивідуально залежно від адреси</li>
            <li>Товар підлягає поверненню протягом 14 днів за умови збереження упаковки</li>
            <li>Термін придатності соків становить 30 днів з дати виробництва</li>
            <li>Зберігати при температурі від +2°C до +6°C</li>
        </ul>
    </div>
    
    <div class="print-footer">
        <p>
            <strong>Дякуємо за ваше замовлення!</strong><br>
            Якщо у вас виникли запитання, будь ласка, зв'яжіться з нами:<br>
            Телефон: +380 (44) 123-45-67, Email: info@juicesales.com<br>
            Сайт: www.juicesales.com
        </p>
        <p class="small-text" style="margin-top: 20px; color: #666;">
            Документ сформовано автоматично системою "<?= APP_NAME ?>" <?= date('d.m.Y H:i') ?>
        </p>
    </div>
</div>