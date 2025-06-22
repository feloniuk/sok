<?php
// app/views/warehouse/inventory.php - Полная исправленная сторінка інвентаризації складу
$title = '';

// Функції для фільтрації та пагінації
function buildFilterUrl($newParams = []) {
    $currentParams = $_GET;
    $params = array_merge($currentParams, $newParams);
    
    // Видалення порожніх параметрів
    foreach ($params as $key => $value) {
        if ($value === '' || $value === null) {
            unset($params[$key]);
        }
    }
    
    return '?' . http_build_query($params);
}

// Функція для генерації штрихкоду з ID продукту
function generateBarcode($productId) {
    return str_pad($productId, 8, '0', STR_PAD_LEFT);
}

// Підключення додаткових CSS
$extra_css = '
<style>
    .filter-card {
        border: none;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .inventory-table th, .inventory-table td {
        vertical-align: middle;
    }
    
    .stock-light {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
    }
    
    .stock-high {
        background-color: #28a745;
    }
    
    .stock-medium {
        background-color: #ffc107;
    }
    
    .stock-low {
        background-color: #dc3545;
    }
    
    .barcode-scanner {
        border: 2px dashed #007bff;
        border-radius: 0.75rem;
        padding: 25px;
        text-align: center;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        margin-bottom: 20px;
        position: relative;
        overflow: hidden;
    }
    
    .barcode-scanner::before {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(0,123,255,0.1), transparent);
        animation: scan-line 3s infinite;
    }
    
    @keyframes scan-line {
        0% { left: -100%; }
        50% { left: 100%; }
        100% { left: 100%; }
    }
    
    .barcode-input {
        font-size: 1.4rem;
        text-align: center;
        letter-spacing: 3px;
        font-family: "Courier New", monospace;
        border: 2px solid #007bff;
        border-radius: 0.5rem;
        padding: 15px;
        background-color: white;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    
    .barcode-input:focus {
        border-color: #0056b3;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25), inset 0 2px 4px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    
    .barcode-help-text {
        color: #6c757d;
        font-size: 0.9rem;
        margin-top: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .barcode-icon {
        font-size: 1.2rem;
        color: #007bff;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    
    .scanner-status-container {
        min-height: 45px;
        margin-bottom: 15px;
    }
    
    .scanner-connection-status {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }
    
    .scanner-connected {
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }
    
    .scanner-disconnected {
        background-color: #fff3cd;
        border: 1px solid #ffeaa7;
        color: #856404;
    }
    
    .scanner-tips {
        background-color: #e7f3ff;
        border: 1px solid #b3d9ff;
        border-radius: 0.5rem;
        padding: 15px;
        margin-top: 15px;
    }
    
    .scanner-tips h6 {
        color: #0056b3;
        margin-bottom: 10px;
        font-weight: 600;
    }
    
    .scanner-tips ul {
        margin-bottom: 0;
        padding-left: 20px;
    }
    
    .scanner-tips li {
        color: #495057;
        margin-bottom: 5px;
        font-size: 0.9rem;
    }
    
    .found-product {
        border: 2px solid #28a745;
        border-radius: 0.75rem;
        padding: 20px;
        margin-bottom: 20px;
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        animation: slideInDown 0.6s ease-out;
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
    }
    
    @keyframes slideInDown {
        from { 
            opacity: 0; 
            transform: translateY(-30px); 
        }
        to { 
            opacity: 1; 
            transform: translateY(0); 
        }
    }
    
    .product-barcode {
        font-family: "Courier New", monospace;
        font-weight: bold;
        color: #333;
        background-color: #fff;
        padding: 4px 8px;
        border: 2px solid #ddd;
        border-radius: 4px;
        display: inline-block;
        min-width: 80px;
        text-align: center;
    }
    
    .quick-scan-btn {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
        border-radius: 50%;
        width: 65px;
        height: 65px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border: none;
        transition: all 0.3s ease;
    }
    
    .quick-scan-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 8px 25px rgba(0,0,0,0.4);
    }
    
    .quick-scan-btn i {
        font-size: 1.3rem;
    }
    
    .scanner-status {
        padding: 12px 15px;
        border-radius: 0.5rem;
        margin-bottom: 15px;
        display: none;
        font-weight: 500;
    }
    
    .scanner-status.success {
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }
    
    .scanner-status.error {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }
    
    .scanner-status.info {
        background-color: #d1ecf1;
        border: 1px solid #bee5eb;
        color: #0c5460;
    }
    
    .barcode-demo {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-top: 10px;
        font-size: 0.85rem;
        color: #6c757d;
    }
    
    .demo-barcode {
        font-family: "Courier New", monospace;
        background-color: #f8f9fa;
        padding: 2px 6px;
        border-radius: 3px;
        border: 1px solid #dee2e6;
    }
    
    .scanner-animation {
        display: inline-block;
        animation: scannerBlink 1.5s infinite;
    }
    
    @keyframes scannerBlink {
        0%, 50% { opacity: 1; }
        51%, 100% { opacity: 0.3; }
    }
</style>';

?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Інвентаризація складу</h1>
            <a href="<?= base_url('warehouse/add_movement') ?>" class="d-none d-sm-inline-block btn btn-success shadow-sm">
                <i class="fas fa-plus-circle fa-sm text-white-50 me-1"></i> Додати рух товару
            </a>
        </div>
    </div>
</div>

<!-- Улучшенный сканер штрихкодов -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-lg">
            <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="m-0">
                        <i class="fas fa-barcode me-2 scanner-animation"></i>
                        Сканування штрихкодів
                    </h5>
                    <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" 
                            data-bs-target="#barcodeInfoModal" title="Довідка">
                        <i class="fas fa-question-circle"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Статус подключения сканера -->
                <div class="scanner-status-container">
                    <div class="scanner-connection-status scanner-disconnected" id="scannerStatus">
                        <i class="fas fa-plug me-2"></i>
                        <span>Підключіть сканер або введіть штрих-код вручну</span>
                    </div>
                </div>
                
                <!-- Форма поиска через GET -->
                <form action="<?= base_url('warehouse/inventory') ?>" method="GET" id="barcodeSearchForm">
                    <input type="hidden" name="search_barcode" value="1">
                    
                    <div class="barcode-scanner">
                        <div class="row">
                            <div class="col-md-10 mx-auto">
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-primary text-white">
                                        <i class="fas fa-barcode barcode-icon"></i>
                                    </span>
                                    <input type="text" class="form-control barcode-input" name="barcode" 
                                           id="barcodeInput"
                                           placeholder="Сканируйте или введите штрих-код..." 
                                           value="<?= $_GET['barcode'] ?? '' ?>" 
                                           autofocus
                                           autocomplete="off">
                                    <button type="submit" class="btn btn-primary btn-lg px-4">
                                        <i class="fas fa-search me-1"></i> Знайти
                                    </button>
                                </div>
                                
                                <div class="barcode-help-text">
                                    <i class="fas fa-info-circle"></i>
                                    <span>Штрих-код формується з ID товару</span>
                                    <span class="mx-2">•</span>
                                    <span>Приклад: товар #123 → код</span>
                                    <span class="demo-barcode">00000123</span>
                                </div>
                                
                                <div class="barcode-demo">
                                    <span>Швидкі клавіші:</span>
                                    <kbd>Ctrl + B</kbd> - фокус на поле
                                    <span class="mx-2">•</span>
                                    <kbd>Enter</kbd> - пошук
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6 mx-auto text-center">
                                <button type="button" class="btn btn-outline-secondary me-2" onclick="clearBarcode()">
                                    <i class="fas fa-times me-1"></i> Очистити
                                </button>
                                <button type="button" class="btn btn-outline-info" onclick="testScanner()">
                                    <i class="fas fa-vial me-1"></i> Тест сканера
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                
                <!-- Сообщения статуса -->
                <div id="scannerMessages"></div>
                
                <!-- Ошибка поиска -->
                <?php if (!empty($searchError)): ?>
                    <div class="alert alert-warning alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle me-2"></i><?= $searchError ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Результат поиска -->
                <?php if (!empty($foundProduct)): ?>
                    <div class="found-product">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <img src="<?= $foundProduct['image'] ? upload_url($foundProduct['image']) : asset_url('images/no-image.jpg') ?>" 
                                     alt="<?= $foundProduct['name'] ?>" class="img-thumbnail shadow-sm" 
                                     style="width: 90px; height: 90px; object-fit: cover;">
                            </div>
                            <div class="col-md-6">
                                <h5 class="mb-2 text-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <?= $foundProduct['name'] ?>
                                </h5>
                                <p class="mb-1">
                                    <strong>Штрих-код:</strong> 
                                    <span class="product-barcode"><?= $foundProduct['barcode'] ?></span>
                                </p>
                                <small class="text-muted">
                                    <i class="fas fa-tag me-1"></i>
                                    Категорія: <?= $foundProduct['category_name'] ?>
                                </small>
                            </div>
                            <div class="col-md-2 text-center">
                                <div class="h5 mb-1 text-primary">
                                    <?= number_format($foundProduct['price'], 2) ?> грн
                                </div>
                                <small class="text-muted">за літр</small>
                            </div>
                            <div class="col-md-2 text-center">
                                <span class="badge bg-primary fs-6 mb-2">
                                    Залишок: <?= $foundProduct['stock_quantity'] ?>
                                </span>
                                <div>
                                    <a href="<?= base_url('warehouse/add_movement?product_id=' . $foundProduct['id']) ?>" 
                                       class="btn btn-success btn-sm">
                                        <i class="fas fa-edit me-1"></i> Додати рух
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Советы по сканированию -->
                <div class="scanner-tips">
                    <h6><i class="fas fa-lightbulb me-2"></i>Поради для ефективного сканування:</h6>
                    <ul>
                        <li>Переконайтеся, що сканер підключений і налаштований як клавіатура</li>
                        <li>Тримайте сканер на відстані 5-15 см від штрих-коду</li>
                        <li>Штрих-код має бути чітким і неушкодженим</li>
                        <li>При ручному введенні використовуйте тільки цифри</li>
                        <li>Для швидкого доступу натисніть <kbd>Ctrl + B</kbd></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Фільтри -->
    <div class="col-md-3 mb-4">
        <div class="card filter-card">
            <div class="card-header bg-primary text-white">
                <h5 class="m-0">Фільтри</h5>
            </div>
            <div class="card-body">
                <form id="filterForm" action="<?= base_url('warehouse/inventory') ?>" method="GET">
                    <!-- Пошук -->
                    <div class="mb-3">
                        <label for="keyword" class="form-label">Пошук</label>
                        <div class="input-group">
                            <input type="text" class="form-control filter-control" id="keyword" name="keyword" 
                                   value="<?= $_GET['keyword'] ?? '' ?>" placeholder="Введіть назву товару...">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Категорія -->
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Категорія</label>
                        <select class="form-select filter-control" id="category_id" name="category_id">
                            <option value="">Всі категорії</option>
                            <?php foreach ($categories ?? [] as $category): ?>
                                <option value="<?= $category['id'] ?>" 
                                        <?= isset($_GET['category_id']) && $_GET['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                    <?= $category['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Діапазон запасів -->
                    <div class="mb-3">
                        <label class="form-label">Залишок на складі</label>
                        <div class="row g-2">
                            <div class="col">
                                <input type="number" class="form-control filter-control" name="min_stock" 
                                       placeholder="Від" value="<?= $_GET['min_stock'] ?? '' ?>">
                            </div>
                            <div class="col">
                                <input type="number" class="form-control filter-control" name="max_stock" 
                                       placeholder="До" value="<?= $_GET['max_stock'] ?? '' ?>">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Сортування -->
                    <div class="mb-3">
                        <label for="sort" class="form-label">Сортування</label>
                        <select class="form-select filter-control" id="sort" name="sort">
                            <option value="name_asc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'name_asc') ? 'selected' : '' ?>>
                                За назвою (А-Я)
                            </option>
                            <option value="name_desc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'name_desc') ? 'selected' : '' ?>>
                                За назвою (Я-А)
                            </option>
                            <option value="stock_asc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'stock_asc') ? 'selected' : '' ?>>
                                За кількістю (зростання)
                            </option>
                            <option value="stock_desc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'stock_desc') ? 'selected' : '' ?>>
                                За кількістю (спадання)
                            </option>
                            <option value="price_asc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'price_asc') ? 'selected' : '' ?>>
                                За ціною (зростання)
                            </option>
                            <option value="price_desc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'price_desc') ? 'selected' : '' ?>>
                                За ціною (спадання)
                            </option>
                        </select>
                    </div>
                    
                    <!-- Тільки товари з низьким запасом -->
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input filter-control" id="low_stock" name="low_stock" 
                               value="1" <?= (isset($_GET['low_stock']) && $_GET['low_stock'] == '1') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="low_stock">
                            Тільки товари з низьким запасом
                        </label>
                    </div>
                    
                    <!-- Кнопки -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i> Застосувати фільтри
                        </button>
                        <button type="button" id="resetFilters" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Скинути фільтри
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Таблиця інвентаризації -->
    <div class="col-md-9">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Товари на складі</h6>
                <div class="dropdown">
                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="exportDropdown" 
                            data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-export me-1"></i> Експорт
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                        <li><a class="dropdown-item" href="<?= base_url('reports/export_inventory?format=csv&' . http_build_query($_GET)) ?>">CSV</a></li>
                        <li><a class="dropdown-item" href="<?= base_url('reports/export_inventory?format=excel&' . http_build_query($_GET)) ?>">Excel</a></li>
                        <li><a class="dropdown-item" href="<?= base_url('reports/export_inventory?format=pdf&' . http_build_query($_GET)) ?>">PDF</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($products)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Товари не знайдені. Спробуйте змінити параметри фільтрації.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover inventory-table">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Штрихкод</th>
                                    <th>Фото</th>
                                    <th>Назва</th>
                                    <th>Категорія</th>
                                    <th>Ціна (грн)</th>
                                    <th>Залишок (літри)</th>
                                    <th>Вартість</th>
                                    <th>Дії</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?= $product['id'] ?></td>
                                        <td>
                                            <span class="product-barcode generate-barcode" data-product-id="<?= $product['id'] ?>">
                                                <?= generateBarcode($product['id']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <img src="<?= $product['image'] ? upload_url($product['image']) : asset_url('images/no-image.jpg') ?>" 
                                                 alt="<?= $product['name'] ?>" class="img-thumbnail" 
                                                 style="width: 50px; height: 50px; object-fit: cover;">
                                        </td>
                                        <td><?= $product['name'] ?></td>
                                        <td><?= isset($product['category_name']) ? $product['category_name'] : '-' ?></td>
                                        <td class="text-end"><?= number_format($product['price'], 2) ?></td>
                                        <td class="text-center">
                                            <?php 
                                            $stockClass = 'stock-high';
                                            if ($product['stock_quantity'] <= 5) {
                                                $stockClass = 'stock-low';
                                            } elseif ($product['stock_quantity'] <= 10) {
                                                $stockClass = 'stock-medium';
                                            }
                                            ?>
                                            <span class="stock-light <?= $stockClass ?>"></span>
                                            <strong><?= $product['stock_quantity'] ?></strong>
                                        </td>
                                        <td class="text-end"><?= number_format($product['price'] * $product['stock_quantity'], 2) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= base_url('warehouse/add_movement?product_id=' . $product['id']) ?>" 
                                                   class="btn btn-primary" title="Додати надходження">
                                                    <i class="fas fa-plus"></i>
                                                </a>
                                                <button type="button" class="btn btn-warning quick-adjust" 
                                                        title="Швидке коригування" 
                                                        data-product-id="<?= $product['id'] ?>" 
                                                        data-product-name="<?= $product['name'] ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="<?= base_url('products/edit/' . $product['id']) ?>" 
                                                   class="btn btn-info" title="Редагувати товар">
                                                    <i class="fas fa-cog"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Пагінація -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($pagination['current_page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= buildFilterUrl(['page' => 1]) ?>">
                                            <i class="fas fa-angle-double-left"></i>
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= buildFilterUrl(['page' => $pagination['current_page'] - 1]) ?>">
                                            <i class="fas fa-angle-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php
                                $startPage = max(1, $pagination['current_page'] - 2);
                                $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
                                
                                for ($i = $startPage; $i <= $endPage; $i++):
                                ?>
                                    <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= buildFilterUrl(['page' => $i]) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= buildFilterUrl(['page' => $pagination['current_page'] + 1]) ?>">
                                            <i class="fas fa-angle-right"></i>
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= buildFilterUrl(['page' => $pagination['total_pages']]) ?>">
                                            <i class="fas fa-angle-double-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Плаваюча кнопка швидкого сканування -->
<button type="button" class="btn btn-primary quick-scan-btn" id="quickScanBtn" title="Швидке сканування">
    <i class="fas fa-barcode fa-lg"></i>
</button>

<!-- Модальне вікно швидкого коригування кількості -->
<div class="modal fade" id="adjustQuantityModal" tabindex="-1" aria-labelledby="adjustQuantityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adjustQuantityModalLabel">Коригування кількості</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('warehouse/storeMovement') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" id="adjustProductId" name="product_id">
                    
                    <p>Ви коригуєте кількість товару: <strong id="adjustProductName"></strong></p>
                    
                    <div class="mb-3">
                        <label for="movement_type" class="form-label">Тип операції</label>
                        <select class="form-select" id="movement_type" name="movement_type" required>
                            <option value="incoming">Надходження</option>
                            <option value="outgoing">Витрата</option>
                            <option value="adjustment">Коригування (інвентаризація)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Кількість</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" required min="1" step="1">
                        <div class="form-text">Вкажіть додатнє число. Якщо це витрата, кількість буде відповідно вирахована.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Примітки</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" 
                                  placeholder="Вкажіть причину коригування"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                    <button type="submit" class="btn btn-primary">Зберегти</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальне вікно інформації про штрихкоди -->
<div class="modal fade" id="barcodeInfoModal" tabindex="-1" aria-labelledby="barcodeInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="barcodeInfoModalLabel">
                    <i class="fas fa-barcode me-2"></i>Інформація про штрихкоди та сканування
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <h6 class="alert-heading">
                                <i class="fas fa-info-circle me-2"></i>Як працюють штрихкоди в системі:
                            </h6>
                            <ul class="list-unstyled mb-0">
                                <li><i class="fas fa-check text-success me-2"></i>Штрихкод автоматично формується з ID товару</li>
                                <li><i class="fas fa-check text-success me-2"></i>Довжина штрихкоду: 8 цифр з ведучими нулями</li>
                                <li><i class="fas fa-check text-success me-2"></i>Приклад: товар #123 → штрихкод <code>00000123</code></li>
                                <li><i class="fas fa-check text-success me-2"></i>Підтримка USB та Bluetooth сканерів</li>
                                <li><i class="fas fa-check text-success me-2"></i>Можливість ручного введення</li>
                            </ul>
                        </div>
                        
                        <h6 class="mt-4">Приклади штрихкодів товарів:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID товару</th>
                                        <th>Штрихкод</th>
                                        <th>Опис</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td><span class="product-barcode">00000001</span></td>
                                        <td>Перший товар в системі</td>
                                    </tr>
                                    <tr>
                                        <td>123</td>
                                        <td><span class="product-barcode">00000123</span></td>
                                        <td>Товар з ID 123</td>
                                    </tr>
                                    <tr>
                                        <td>9999</td>
                                        <td><span class="product-barcode">00009999</span></td>
                                        <td>Товар з великим ID</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h6><i class="fas fa-scanner me-2 text-primary"></i>Налаштування сканера:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-arrow-right text-muted me-2"></i>Переведіть сканер в режим "Keyboard Emulation"</li>
                                    <li><i class="fas fa-arrow-right text-muted me-2"></i>Налаштуйте автоматичний Enter після сканування</li>
                                    <li><i class="fas fa-arrow-right text-muted me-2"></i>Перевірте швидкість сканування (рекомендовано: середня)</li>
                                    <li><i class="fas fa-arrow-right text-muted me-2"></i>Увімкніть звуковий сигнал підтвердження</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-keyboard me-2 text-success"></i>Гарячі клавіші:</h6>
                                <ul class="list-unstyled">
                                    <li><kbd>Ctrl + B</kbd> - Фокус на поле штрихкоду</li>
                                    <li><kbd>Enter</kbd> - Почати пошук</li>
                                    <li><kbd>Esc</kbd> - Очистити поле</li>
                                    <li><kbd>F3</kbd> - Тест сканера</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="alert alert-success mt-4">
                            <h6 class="alert-heading">
                                <i class="fas fa-lightbulb me-2"></i>Поради для ефективної роботи:
                            </h6>
                            <ul class="mb-0">
                                <li>Використовуйте плаваючу кнопку <span class="badge bg-primary"><i class="fas fa-barcode"></i></span> для швидкого доступу до сканування</li>
                                <li>При поганому освітленні збільшіть відстань між сканером та штрихкодом</li>
                                <li>Регулярно очищуйте лінзу сканера для кращої якості сканування</li>
                                <li>У разі проблем з сканером скористайтеся ручним введенням</li>
                                <li>Перевіряйте заряд батареї бездротових сканерів</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрити</button>
                <button type="button" class="btn btn-primary" onclick="testScanner()">
                    <i class="fas fa-vial me-1"></i>Тест сканера
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Додаткова кнопка інформації про штрихкоди -->
<div class="position-fixed" style="bottom: 95px; right: 20px; z-index: 1000;">
    <button type="button" class="btn btn-info btn-sm rounded-circle shadow" data-bs-toggle="modal" 
            data-bs-target="#barcodeInfoModal" title="Інформація про штрихкоди">
        <i class="fas fa-question"></i>
    </button>
</div>

<script>
// Глобальные переменные для работы со сканером
let scannerConnected = false;
let lastScanTime = 0;
let scanBuffer = '';

$(document).ready(function() {
    // Инициализация сканера штрихкодов
    initBarcodeScanner();
    
    // Фільтрація інвентаря при зміні параметрів
    $(".filter-control").on("change", function() {
        $("#filterForm").submit();
    });
    
    // Скидання фільтрів
    $("#resetFilters").on("click", function() {
        $(".filter-control").each(function() {
            if ($(this).is("select")) {
                $(this).val("");
            } else {
                $(this).val("");
            }
        });
        $("#filterForm").submit();
    });
    
    // Функція для швидкого коригування кількості
    $(document).on("click", ".quick-adjust", function() {
        const productId = $(this).data("product-id");
        const productName = $(this).data("product-name");
        
        $("#adjustProductId").val(productId);
        $("#adjustProductName").text(productName);
        $("#adjustQuantityModal").modal("show");
    });
    
    // Генерація штрихкоду для існуючих товарів
    $(".generate-barcode").each(function() {
        const productId = $(this).data("product-id");
        const barcode = String(productId).padStart(8, "0");
        $(this).text(barcode);
    });
    
    // Плавающая кнопка быстрого сканирования
    $("#quickScanBtn").on("click", function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log("Quick scan button clicked");
        focusOnBarcodeInput();
    });
    
    // Горячие клавиши
    $(document).on("keydown", function(e) {
        // Ctrl + B - фокус на поле штрихкода
        if (e.ctrlKey && (e.key === 'b' || e.key === 'B' || e.keyCode === 66)) {
            e.preventDefault();
            e.stopPropagation();
            console.log("Ctrl+B pressed, focusing barcode input");
            focusOnBarcodeInput();
            return false;
        }
        
        // F3 - тест сканера
        if (e.key === 'F3' || e.keyCode === 114) {
            e.preventDefault();
            testScanner();
        }
        
        // Esc - очистка поля штрихкода
        if ((e.key === 'Escape' || e.keyCode === 27) && document.activeElement && document.activeElement.id === 'barcodeInput') {
            clearBarcode();
        }
    });
    
    // Прокрутка к найденному товару если он есть
    <?php if (!empty($foundProduct)): ?>
        const foundProductId = <?= $foundProduct['id'] ?>;
        highlightFoundProduct(foundProductId);
    <?php endif; ?>
    
    // Обновление статуса сканера каждые 5 секунд
    setInterval(updateScannerStatus, 5000);
});

// Инициализация сканера штрихкодов
function initBarcodeScanner() {
    const barcodeInput = $("#barcodeInput");
    
    // Обработка ввода в поле штрихкода
    barcodeInput.on("input", function(e) {
        const value = $(this).val();
        
        // Проверка на быстрый ввод (характерно для сканера)
        const currentTime = Date.now();
        if (currentTime - lastScanTime < 100 && value.length > 3) {
            scannerConnected = true;
            showScannerStatus("success", "Сканер підключений і працює");
            
            // Автоматический поиск при сканировании
            setTimeout(() => {
                $("#barcodeSearchForm").submit();
            }, 100);
        }
        lastScanTime = currentTime;
        
        // Фильтрация только цифр
        const cleanValue = value.replace(/\D/g, '');
        if (cleanValue !== value) {
            $(this).val(cleanValue);
        }
    });
    
    // Обработка фокуса
    barcodeInput.on("focus", function() {
        $(this).select();
        showScannerStatus("info", "Готово до сканування або ручного введення");
    });
    
    // Обработка потери фокуса
    barcodeInput.on("blur", function() {
        setTimeout(() => {
            hideScannerStatus();
        }, 2000);
    });
    
    // Автофокус при загрузке страницы
    barcodeInput.focus();
}

// Функция фокуса на поле штрихкода
function focusOnBarcodeInput() {
    const barcodeInput = $("#barcodeInput");
    
    // Убедимся что элемент существует
    if (barcodeInput.length === 0) {
        console.error("Barcode input field not found!");
        return;
    }
    
    // Прокрутка к полю сначала
    $("html, body").animate({
        scrollTop: barcodeInput.offset().top - 150
    }, 300, function() {
        // После завершения прокрутки устанавливаем фокус
        setTimeout(function() {
            barcodeInput[0].focus(); // Используем нативный focus
            barcodeInput[0].select(); // Используем нативный select
            
            // Дополнительная проверка фокуса
            if (document.activeElement !== barcodeInput[0]) {
                // Если фокус не установился, пробуем еще раз
                barcodeInput.trigger('focus');
            }
        }, 100);
    });
    
    showScannerStatus("info", "Поле активне. Сканируйте штрих-код або введіть вручну");
}

// Очистка поля штрихкода
function clearBarcode() {
    const barcodeInput = $("#barcodeInput");
    barcodeInput.val("");
    
    // Устанавливаем фокус
    setTimeout(function() {
        barcodeInput[0].focus();
    }, 50);
    
    showScannerStatus("info", "Поле очищене. Готово до нового сканування");
}

// Тест сканера
function testScanner() {
    showScannerStatus("info", "Тестування сканера... Просканируйте будь-який штрих-код");
    
    const barcodeInput = $("#barcodeInput");
    
    // Сначала очищаем и фокусируемся
    barcodeInput.val("");
    setTimeout(function() {
        barcodeInput[0].focus();
        barcodeInput[0].select();
    }, 100);
    
    // Таймер теста
    let testTimeout = setTimeout(() => {
        showScannerStatus("error", "Тест не пройден. Перевірте підключення сканера");
    }, 10000);
    
    // Обработчик для теста
    const testHandler = function() {
        clearTimeout(testTimeout);
        showScannerStatus("success", "Тест пройшов успішно! Сканер працює коректно");
        barcodeInput.off("input", testHandler);
    };
    
    barcodeInput.on("input", testHandler);
}

// Показ статуса сканера
function showScannerStatus(type, message) {
    const statusContainer = $("#scannerMessages");
    const alertClass = type === "success" ? "alert-success" : 
                      type === "error" ? "alert-danger" : "alert-info";
    
    const iconClass = type === "success" ? "fa-check-circle" : 
                     type === "error" ? "fa-exclamation-triangle" : "fa-info-circle";
    
    const statusHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas ${iconClass} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    statusContainer.html(statusHtml);
    
    // Автоматическое скрытие через 5 секунд
    setTimeout(() => {
        statusContainer.find(".alert").fadeOut();
    }, 5000);
}

// Скрытие статуса сканера
function hideScannerStatus() {
    $("#scannerMessages").fadeOut();
}

// Обновление статуса подключения сканера
function updateScannerStatus() {
    const statusElement = $("#scannerStatus");
    const currentTime = Date.now();
    
    if (currentTime - lastScanTime > 30000) {
        scannerConnected = false;
    }
    
    if (scannerConnected) {
        statusElement.removeClass("scanner-disconnected").addClass("scanner-connected");
        statusElement.html('<i class="fas fa-check-circle me-2"></i><span>Сканер підключений і готовий до роботи</span>');
    } else {
        statusElement.removeClass("scanner-connected").addClass("scanner-disconnected");
        statusElement.html('<i class="fas fa-plug me-2"></i><span>Підключіть сканер або введіть штрих-код вручну</span>');
    }
}

// Подсветка найденного товара
function highlightFoundProduct(productId) {
    const $row = $(`.inventory-table tbody tr`).filter(function() {
        return $(this).find("td:first").text().trim() == productId;
    });
    
    if ($row.length > 0) {
        // Подсветить строку
        $row.addClass("table-warning");
        
        // Прокрутить к строке
        $("html, body").animate({
            scrollTop: $row.offset().top - 150
        }, 1000);
        
        // Убрать подсветку через 8 секунд
        setTimeout(function() {
            $row.removeClass("table-warning");
        }, 8000);
    }
}
</script>