<?php
// app/views/sales/dashboard.php - Панель керування менеджера продажів
$title = 'Панель керування менеджера продажів';

// Підключення додаткових CSS
$extra_css = '
<style>
    .stat-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    .stat-icon {
        font-size: 2.5rem;
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
</style>';

// Підключення додаткових JS
$extra_js = '
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        const baseUrl = "' . base_url() . '";
        
        // Графік продажів
        const salesCtx = document.getElementById("salesChart").getContext("2d");
        const salesChart = new Chart(salesCtx, {
            type: "line",
            data: {
                labels: ' . json_encode(array_column($salesStats["ordersByDate"] ?? [], "date")) . ',
                datasets: [{
                    label: "Виручка",
                    data: ' . json_encode(array_column($salesStats["ordersByDate"] ?? [], "amount")) . ',
                    borderColor: "#4e73df",
                    backgroundColor: "rgba(78, 115, 223, 0.05)",
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: "Динаміка продажів"
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: "Сума (грн)"
                        }
                    }
                }
            }
        });
        
        // Графік найпопулярніших продуктів
        const topProductsCtx = document.getElementById("topProductsChart").getContext("2d");
        new Chart(topProductsCtx, {
            type: "bar",
            data: {
                labels: ' . json_encode(array_column($salesStats["topSellingProducts"] ?? [], "name")) . ',
                datasets: [{
                    label: "Кількість проданих одиниць",
                    data: ' . json_encode(array_column($salesStats["topSellingProducts"] ?? [], "total_sold")) . ',
                    backgroundColor: "#4e73df"
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: "Топ-5 продуктів за кількістю продажів"
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: "Кількість одиниць"
                        }
                    }
                }
            }
        });
        
        // Графік замовлень за статусами
        const ordersCtx = document.getElementById("ordersStatusChart").getContext("2d");
        new Chart(ordersCtx, {
            type: "doughnut",
            data: {
                labels: ' . json_encode(array_column($orderStats["ordersByStatus"] ?? [], "status")) . ',
                datasets: [{
                    data: ' . json_encode(array_column($orderStats["ordersByStatus"] ?? [], "count")) . ',
                    backgroundColor: [
                        "#4e73df", // pending
                        "#f6c23e", // processing
                        "#36b9cc", // shipped
                        "#1cc88a", // delivered
                        "#e74a3b", // cancelled
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: "Замовлення за статусами"
                    }
                }
            }
        });
        
        // Зміна періоду
        $(".chart-period").on("click", function(e) {
            e.preventDefault();
            const period = $(this).data("period");
            
            // Оновлення активного елементу
            $(".chart-period").removeClass("active");
            $(this).addClass("active");
            
            // Запит нових даних
            $.ajax({
                url: baseUrl + "dashboard/chart_data",
                method: "GET",
                data: { type: "sales", period: period },
                dataType: "json",
                success: function(data) {
                    // Оновлення графіка
                    salesChart.data.labels = data.map(item => item.day);
                    salesChart.data.datasets[0].data = data.map(item => item.amount);
                    salesChart.update();
                }
            });
        });
    });
</script>';

// Функція для перетворення статусів замовлень
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
?>

<div class="row dashboard-stats mb-4">
    <div class="col-md-3">
        <div class="card border-left-primary shadow h-100 py-2 stat-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Загальний дохід
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format($orderStats['totalAmount'] ?? 0, 2) ?> грн.
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="stat-icon bg-primary text-white">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-left-success shadow h-100 py-2 stat-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Кількість замовлень
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format($orderStats['totalOrders'] ?? 0) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="stat-icon bg-success text-white">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-left-info shadow h-100 py-2 stat-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Кількість клієнтів
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format($salesStats['customersCount'] ?? 0) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="stat-icon bg-info text-white">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-left-warning shadow h-100 py-2 stat-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Середній чек
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= ($orderStats['totalOrders'] ?? 0) > 0 ? 
                                number_format(($orderStats['totalAmount'] ?? 0) / ($orderStats['totalOrders'] ?? 1), 2) : 
                                '0.00' ?> грн.
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="stat-icon bg-warning text-white">
                            <i class="fas fa-receipt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mb-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">Динаміка продажів</h6>
                <div class="btn-group btn-group-sm">
                    <a href="#" class="btn btn-light chart-period active" data-period="week">Тиждень</a>
                    <a href="#" class="btn btn-light chart-period" data-period="month">Місяць</a>
                    <a href="#" class="btn btn-light chart-period" data-period="quarter">Квартал</a>
                    <a href="#" class="btn btn-light chart-period" data-period="year">Рік</a>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-area" style="height: 300px;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <h6 class="m-0 font-weight-bold">Замовлення за статусами</h6>
            </div>
            <div class="card-body">
                <div class="chart-area" style="height: 300px;">
                    <canvas id="ordersStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h6 class="m-0 font-weight-bold">Топ продуктів за продажами</h6>
            </div>
            <div class="card-body">
                <div class="chart-area" style="height: 300px;">
                    <canvas id="topProductsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark">
                <h6 class="m-0 font-weight-bold">Топ клієнтів</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Клієнт</th>
                                <th>Замовлень</th>
                                <th>Сума витрат</th>
                                <th>Дії</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topCustomers ?? [] as $customer): ?>
                                <tr>
                                    <td><?= $customer['first_name'] . ' ' . $customer['last_name'] ?></td>
                                    <td><?= $customer['order_count'] ?></td>
                                    <td><?= number_format($customer['total_spent'], 2) ?> грн.</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= base_url('orders?customer_id=' . $customer['id']) ?>" class="btn btn-outline-primary" title="Переглянути замовлення">
                                                <i class="fas fa-shopping-cart"></i>
                                            </a>
                                            <a href="<?= base_url('orders/create?customer_id=' . $customer['id']) ?>" class="btn btn-outline-success" title="Нове замовлення">
                                                <i class="fas fa-plus-circle"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($topCustomers)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">Немає даних про клієнтів</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow">
            <div class="card-header bg-secondary text-white">
                <h6 class="m-0 font-weight-bold">Останні замовлення</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>№</th>
                                <th>Клієнт</th>
                                <th>Сума</th>
                                <th>Статус</th>
                                <th>Дата</th>
                                <th>Дії</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders ?? [] as $order): ?>
                                <tr>
                                    <td><?= $order['order_number'] ?></td>
                                    <td><?= $order['first_name'] . ' ' . $order['last_name'] ?></td>
                                    <td><?= number_format($order['total_amount'], 2) ?> грн.</td>
                                    <td>
                                        <span class="badge bg-<?= getStatusClass($order['status']) ?>">
                                            <?= getStatusName($order['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d.m.Y', strtotime($order['created_at'])) ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= base_url('orders/view/' . $order['id']) ?>" class="btn btn-outline-primary" title="Переглянути">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($order['status'] == 'pending'): ?>
                                                <a href="<?= base_url('orders/update_status/' . $order['id']) ?>" class="btn btn-outline-info" title="Обробити">
                                                    <i class="fas fa-cog"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="<?= base_url('orders/print/' . $order['id']) ?>" class="btn btn-outline-secondary" title="Друкувати">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recentOrders)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Немає нових замовлень</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="<?= base_url('orders') ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-eye me-1"></i> Переглянути всі замовлення
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow">
            <div class="card-header bg-dark text-white">
                <h6 class="m-0 font-weight-bold">Швидкі дії</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('orders/create') ?>" class="btn btn-primary btn-block w-100">
                            <i class="fas fa-cart-plus me-2"></i> Створити замовлення
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('orders?status=pending') ?>" class="btn btn-warning btn-block w-100">
                            <i class="fas fa-clock me-2"></i> Очікуючі замовлення
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('reports/sales') ?>" class="btn btn-success btn-block w-100">
                            <i class="fas fa-chart-line me-2"></i> Статистика продажів
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('reports/generate') ?>" class="btn btn-info btn-block w-100">
                            <i class="fas fa-file-export me-2"></i> Створити звіт
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>