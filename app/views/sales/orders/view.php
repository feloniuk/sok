<?php
// app/views/sales/orders/view.php - Перегляд замовлення для менеджера продажів

// Визначення класів статусів для відображення
function getStatusClass($status) {
    $statusClasses = [
        'pending' => 'warning',
        'processing' => 'info',
        'shipped' => 'primary',
        'delivered' => 'success',
        'cancelled' => 'danger'
    ];
    return $statusClasses[$status] ?? 'secondary';
}

// Отримання назви статусу замовлення українською
function getStatusName($status) {
    $statusNames = [
        'pending' => 'Очікує',
        'processing' => 'Обробляється',
        'shipped' => 'Відправлено',
        'delivered' => 'Доставлено',
        'cancelled' => 'Скасовано'
    ];
    return $statusNames[$status] ?? $status;
}

// Отримання назви способу оплати українською
function getPaymentMethodName($method) {
    $methodNames = [
        'credit_card' => 'Кредитна картка',
        'bank_transfer' => 'Банківський переказ',
        'cash_on_delivery' => 'Накладений платіж'
    ];
    return $methodNames[$method] ?? $method;
}
?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Замовлення №<?= $order['order_number'] ?></h6>
        <div class="dropdown no-arrow">
            <a class="dropdown-toggle" href="#" role="button" id="orderActionsDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="orderActionsDropdown">
                <a class="dropdown-item" href="<?= base_url('orders/print/' . $order['id']) ?>" target="_blank">
                    <i class="fas fa-print fa-sm fa-fw mr-2 text-gray-400"></i>
                    Друкувати
                </a>
                <?php if ($order['status'] == 'pending'): ?>
                    <form action="<?= base_url('orders/update_status/' . $order['id']) ?>" method="post" class="d-inline">
                        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">
                        <input type="hidden" name="status" value="processing">
                        <button type="submit" class="dropdown-item">
                            <i class="fas fa-check fa-sm fa-fw mr-2 text-gray-400"></i>
                            Прийняти в обробку
                        </button>
                    </form>
                <?php elseif ($order['status'] == 'processing'): ?>
                    <form action="<?= base_url('orders/update_status/' . $order['id']) ?>" method="post" class="d-inline">
                        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">
                        <input type="hidden" name="status" value="shipped">
                        <button type="submit" class="dropdown-item">
                            <i class="fas fa-truck fa-sm fa-fw mr-2 text-gray-400"></i>
                            Відмітити як відправлене
                        </button>
                    </form>
                <?php elseif ($order['status'] == 'shipped'): ?>
                    <form action="<?= base_url('orders/update_status/' . $order['id']) ?>" method="post" class="d-inline">
                        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">
                        <input type="hidden" name="status" value="delivered">
                        <button type="submit" class="dropdown-item">
                            <i class="fas fa-check-circle fa-sm fa-fw mr-2 text-gray-400"></i>
                            Відмітити як доставлене
                        </button>
                    </form>
                <?php endif; ?>
                
                <?php if (in_array($order['status'], ['pending', 'processing'])): ?>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="<?= base_url('orders/cancel/' . $order['id']) ?>" 
                       onclick="return confirm('Ви впевнені, що хочете скасувати це замовлення?');">
                        <i class="fas fa-times fa-sm fa-fw mr-2 text-danger"></i>
                        Скасувати замовлення
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <div class="row mb-4">
            <!-- Інформація про замовлення -->
            <div class="col-md-6">
                <div class="card border-left-primary h-100">
                    <div class="card-body">
                        <h5 class="card-title">Інформація про замовлення</h5>
                        <div class="row mb-2">
                            <div class="col-md-6"><strong>Номер замовлення:</strong></div>
                            <div class="col-md-6"><?= $order['order_number'] ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6"><strong>Статус:</strong></div>
                            <div class="col-md-6">
                                <span class="badge bg-<?= getStatusClass($order['status']) ?>">
                                    <?= getStatusName($order['status']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6"><strong>Дата замовлення:</strong></div>
                            <div class="col-md-6"><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6"><strong>Спосіб оплати:</strong></div>
                            <div class="col-md-6"><?= getPaymentMethodName($order['payment_method']) ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6"><strong>Сума замовлення:</strong></div>
                            <div class="col-md-6"><?= number_format($order['total_amount'], 2) ?> грн</div>
                        </div>
                        <?php if (!empty($order['notes'])): ?>
                            <div class="row mb-2">
                                <div class="col-md-6"><strong>Примітки:</strong></div>
                                <div class="col-md-6"><?= $order['notes'] ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Інформація про клієнта -->
            <div class="col-md-6">
                <div class="card border-left-success h-100">
                    <div class="card-body">
                        <h5 class="card-title">Інформація про клієнта</h5>
                        <div class="row mb-2">
                            <div class="col-md-6"><strong>Ім'я клієнта:</strong></div>
                            <div class="col-md-6"><?= $order['first_name'] . ' ' . $order['last_name'] ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6"><strong>Email:</strong></div>
                            <div class="col-md-6"><?= $order['email'] ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6"><strong>Телефон:</strong></div>
                            <div class="col-md-6"><?= $order['phone'] ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6"><strong>Адреса доставки:</strong></div>
                            <div class="col-md-6"><?= $order['shipping_address'] ?></div>
                        </div>
                        
                        <div class="mt-3">
                            <a href="<?= base_url('orders?customer_id=' . $order['customer_id']) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-history"></i> Історія замовлень клієнта
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Товари в замовленні -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Товари в замовленні</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th width="60">Фото</th>
                                <th>Товар</th>
                                <th>Ціна</th>
                                <th>Кількість</th>
                                <th>Сума</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($item['image'])): ?>
                                            <img src="<?= upload_url($item['image']) ?>" alt="<?= $item['product_name'] ?>" class="img-thumbnail" width="50">
                                        <?php else: ?>
                                            <img src="<?= asset_url('images/no-image.jpg') ?>" alt="No Image" class="img-thumbnail" width="50">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= base_url('products/view/' . $item['product_id']) ?>"><?= $item['product_name'] ?></a>
                                    </td>
                                    <td><?= number_format($item['price'], 2) ?> грн</td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td><?= number_format($item['price'] * $item['quantity'], 2) ?> грн</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-end">Загальна сума:</th>
                                <th><?= number_format($order['total_amount'], 2) ?> грн</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Історія статусів -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Історія змін статусу</h6>
                    </div>
                    <div class="card-body">
                        <!-- В реальному проекті тут має бути історія змін статусу замовлення -->
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h5 class="timeline-title">Замовлення створено</h5>
                                    <p class="timeline-text">
                                        <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?>
                                    </p>
                                </div>
                            </div>
                            
                            <?php if ($order['status'] != 'pending' && $order['status'] != 'cancelled'): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <h5 class="timeline-title">Замовлення в обробці</h5>
                                        <p class="timeline-text">
                                            <?= date('d.m.Y H:i', strtotime($order['updated_at'])) ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($order['status'] == 'shipped' || $order['status'] == 'delivered'): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h5 class="timeline-title">Замовлення відправлено</h5>
                                        <p class="timeline-text">
                                            <?= date('d.m.Y H:i', strtotime($order['updated_at'])) ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($order['status'] == 'delivered'): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h5 class="timeline-title">Замовлення доставлено</h5>
                                        <p class="timeline-text">
                                            <?= date('d.m.Y H:i', strtotime($order['updated_at'])) ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($order['status'] == 'cancelled'): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-danger"></div>
                                    <div class="timeline-content">
                                        <h5 class="timeline-title">Замовлення скасовано</h5>
                                        <p class="timeline-text">
                                            <?= date('d.m.Y H:i', strtotime($order['updated_at'])) ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Кнопки дій -->
        <div class="row mt-4">
            <div class="col-md-12 d-flex justify-content-between">
                <a href="<?= base_url('orders') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Назад до списку замовлень
                </a>
                
                <div>
                    <a href="<?= base_url('orders/print/' . $order['id']) ?>" class="btn btn-secondary" target="_blank">
                        <i class="fas fa-print"></i> Друкувати
                    </a>
                    
                    <?php if ($order['status'] == 'pending'): ?>
                        <form action="<?= base_url('orders/update_status/' . $order['id']) ?>" method="post" class="d-inline">
                            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">
                            <input type="hidden" name="status" value="processing">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-box"></i> Прийняти в обробку
                            </button>
                        </form>
                    <?php elseif ($order['status'] == 'processing'): ?>
                        <form action="<?= base_url('orders/update_status/' . $order['id']) ?>" method="post" class="d-inline">
                            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">
                            <input type="hidden" name="status" value="shipped">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-truck"></i> Відмітити як відправлене
                            </button>
                        </form>
                    <?php elseif ($order['status'] == 'shipped'): ?>
                        <form action="<?= base_url('orders/update_status/' . $order['id']) ?>" method="post" class="d-inline">
                            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">
                            <input type="hidden" name="status" value="delivered">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check-circle"></i> Відмітити як доставлене
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <?php if (in_array($order['status'], ['pending', 'processing'])): ?>
                        <a href="<?= base_url('orders/cancel/' . $order['id']) ?>" class="btn btn-danger" 
                           onclick="return confirm('Ви впевнені, що хочете скасувати це замовлення?');">
                            <i class="fas fa-times"></i> Скасувати замовлення
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Стилі для Timeline */
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    
    .timeline:before {
        content: '';
        position: absolute;
        left: 9px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }
    
    .timeline-marker {
        position: absolute;
        left: -30px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
    }
    
    .timeline-content {
        padding-bottom: 10px;
        border-bottom: 1px dashed #e9ecef;
    }
    
    .timeline-title {
        margin-bottom: 5px;
        font-size: 16px;
    }
    
    .timeline-text {
        color: #6c757d;
        margin-bottom: 0;
    }
</style>