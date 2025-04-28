<?php
// app/views/orders/print.php - Сторінка для друку замовлення
$title = 'Друк замовлення';

// Функції для форматування
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

// Додаткові CSS для сторінки друку
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
</style>';

// Додаткові JS для сторінки друку
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
            <p>від <?= date('d.m.Y', strtotime($order['created_at'])) ?></p>
        </div>
        <div>
            <img src="<?= asset_url('images/logo.png') ?>" alt="<?= APP_NAME ?>" height="50">
            <p>
                ТОВ "Соки України"<br>
                Код ЄДРПОУ: 12345678<br>
                м. Київ, вул. Соняшникова, 10
            </p>
        </div>
    </div>
    
    <div class="order-details">
        <div class="order-details-left">
            <h3>Інформація про замовлення</h3>
            <p><strong>Статус:</strong> <?= getStatusName($order['status']) ?></p>
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
                <th style="width: 80px;" class="text-end">Кількість</th>
                <th style="width: 100px;" class="text-end">Ціна</th>
                <th style="width: 120px;" class="text-end">Сума</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orderItems as $index => $item): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= $item['product_name'] ?></td>
                    <td class="text-end"><?= $item['quantity'] ?></td>
                    <td class="text-end"><?= number_format($item['price'], 2) ?></td>
                    <td class="text-end"><?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-end"><strong>Всього:</strong></td>
                <td class="text-end"><strong><?= number_format($order['total_amount'], 2) ?> грн</strong></td>
            </tr>
        </tfoot>
    </table>
    
    <div class="signature-area">
        <div class="signature-box">
            <span class="small-text">Підпис продавця</span>
        </div>
        <div class="signature-box">
            <span class="small-text">Підпис покупця</span>
        </div>
    </div>
    
    <div class="print-footer">
        <p>
            Дякуємо за ваше замовлення!<br>
            Якщо у вас виникли запитання, будь ласка, зв'яжіться з нами:<br>
            Телефон: +380 (44) 123-45-67, Email: info@juicesales.com
        </p>
        <p class="small-text">Документ сформовано автоматично системою "<?= APP_NAME ?>"</p>
    </div>
</div>