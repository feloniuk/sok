<?php
// app/views/warehouse/inventory.php - Полная исправленная сторінка інвентаризації складу
$title = 'Інвентаризація складу';

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
        border-radius: 0.5rem;
        padding: 20px;
        text-align: center;
        background-color: #f8f9fa;
        margin-bottom: 20px;
    }
    
    .barcode-input {
        font-size: 1.2rem;
        text-align: center;
        letter-spacing: 2px;
    }
    
    .found-product {
        border: 2px solid #28a745;
        border-radius: 0.5rem;
        padding: 15px;
        margin-bottom: 20px;
        background-color: #d4edda;
        animation: fadeIn 0.5s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .product-barcode {
        font-family: "Courier New", monospace;
        font-weight: bold;
        color: #333;
        background-color: #fff;
        padding: 2px 5px;
        border: 1px solid #ddd;
        border-radius: 3px;
    }
    
    .quick-scan-btn {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
        border-radius: 50%;
        width: 60px;
        height: 60px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }
    
    .scanner-status {
        padding: 10px;
        border-radius: 0.25rem;
        margin-bottom: 15px;
        display: none;
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
</style>';

// Підключення додаткових JS
$extra_js = '
<script>
    $(document).ready(function() {
        // Змінні для роботи зі сканером
        let scannerActive = false;
        let scanBuffer = "";
        let scanTimeout;
        
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
        
        // Обробка вводу штрихкоду
        $("#barcodeInput").on("keypress", function(e) {
            if (e.which === 13) { // Enter
                e.preventDefault();
                searchByBarcode();
            }
        });
        
        // Кнопка пошуку по штрихкоду
        $("#searchBarcodeBtn").on("click", function() {
            searchByBarcode();
        });
        
        // Очищення результату пошуку
        $("#clearSearchBtn").on("click", function() {
            clearBarcodeSearch();
        });
        
        // Швидке сканування (плаваюча кнопка)
        $("#quickScanBtn").on("click", function() {
            $("#barcodeInput").focus();
            $("#barcodeInput").select();
        });
        
        // Автоматичне сканування зі сканера штрихкодів
        $(document).on("keydown", function(e) {
            // Якщо фокус не на input полі і натиснута цифра або Enter
            if (!$(e.target).is("input, textarea, select") && 
                ((e.which >= 48 && e.which <= 57) || // 0-9
                 (e.which >= 96 && e.which <= 105) || // Numpad 0-9
                 e.which === 13)) { // Enter
                
                if (e.which === 13 && scanBuffer.length > 0) {
                    // Enter - завершити сканування
                    $("#barcodeInput").val(scanBuffer);
                    searchByBarcode();
                    scanBuffer = "";
                } else if (e.which !== 13) {
                    // Додати цифру до буфера
                    scanBuffer += String.fromCharCode(e.which);
                    
                    // Очистити буфер через 100ms якщо нічого не вводиться
                    clearTimeout(scanTimeout);
                    scanTimeout = setTimeout(function() {
                        scanBuffer = "";
                    }, 100);
                }
            }
        });
        
        // Функція пошуку товару по штрихкоду
        function searchByBarcode() {
            const barcode = $("#barcodeInput").val().trim();
            
            if (!barcode) {
                showScannerStatus("Введіть штрихкод для пошуку", "error");
                return;
            }
            
            // Показати індикатор завантаження
            $("#searchBarcodeBtn").prop("disabled", true).html("<i class=\"fas fa-spinner fa-spin\"></i> Пошук...");
            
            // AJAX запит для пошуку товару
            $.ajax({
                url: "' . base_url('warehouse/searchByBarcode') . '",
                method: "POST",
                data: {
                    barcode: barcode
                },
                dataType: "json",
                success: function(response) {
                    console.log("Response:", response); // Для відладки
                    
                    if (response.success) {
                        displayFoundProduct(response.product);
                        showScannerStatus("Товар знайдено!", "success");
                        
                        // Прокрутити до знайденого товару в таблиці
                        scrollToProductInTable(response.product.id);
                    } else {
                        showScannerStatus(response.error || "Товар не знайдено", "error");
                        clearFoundProduct();
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", xhr.responseText); // Для відладки
                    
                    let errorMessage = "Помилка при пошуку товару";
                    
                    // Спроба витягнути повідомлення про помилку з відповіді
                    try {
                        const errorResponse = JSON.parse(xhr.responseText);
                        if (errorResponse.error) {
                            errorMessage = errorResponse.error;
                        }
                    } catch (e) {
                        // Якщо не вдалося розпарсити JSON, використовуємо стандартне повідомлення
                    }
                    
                    showScannerStatus(errorMessage, "error");
                    clearFoundProduct();
                },
                complete: function() {
                    $("#searchBarcodeBtn").prop("disabled", false).html("<i class=\"fas fa-search\"></i> Знайти");
                }
            });
        }
        
        // Відображення знайденого товару
        function displayFoundProduct(product) {
            const productHtml = `
                <div class="row align-items-center">
                    <div class="col-md-2">
                        <img src="${product.image}" alt="${product.name}" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-1">${product.name}</h5>
                        <p class="mb-1">Штрихкод: <span class="product-barcode">${product.barcode}</span></p>
                        <small class="text-muted">Категорія: ${product.category_name}</small>
                    </div>
                    <div class="col-md-2">
                        <strong>Ціна: ${parseFloat(product.price).toFixed(2)} грн</strong>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <span class="badge bg-primary fs-6">Залишок: ${product.stock_quantity}</span>
                            <div class="mt-2">
                                <button type="button" class="btn btn-success btn-sm quick-adjust" 
                                        data-product-id="${product.id}" data-product-name="${product.name}">
                                    <i class="fas fa-edit"></i> Коригувати
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $("#foundProduct").html(productHtml).show();
        }
        
        // Очищення результату пошуку
        function clearFoundProduct() {
            $("#foundProduct").hide().empty();
        }
        
        // Очищення всього пошуку
        function clearBarcodeSearch() {
            $("#barcodeInput").val("");
            clearFoundProduct();
            $("#scannerStatus").hide();
        }
        
        // Показ статусу сканера
        function showScannerStatus(message, type) {
            $("#scannerStatus")
                .removeClass("success error")
                .addClass(type)
                .text(message)
                .show();
            
            // Автоматично сховати через 3 секунди
            setTimeout(function() {
                $("#scannerStatus").fadeOut();
            }, 3000);
        }
        
        // Прокрутка до товару в таблиці
        function scrollToProductInTable(productId) {
            const $row = $(`.inventory-table tbody tr`).filter(function() {
                return $(this).find("td:first").text().trim() == productId;
            });
            
            if ($row.length > 0) {
                // Підсвітити рядок
                $row.addClass("table-warning");
                
                // Прокрутити до рядка
                $("html, body").animate({
                    scrollTop: $row.offset().top - 100
                }, 1000);
                
                // Прибрати підсвічування через 3 секунди
                setTimeout(function() {
                    $row.removeClass("table-warning");
                }, 3000);
            }
        }
        
        // Генерація штрихкоду для існуючих товарів
        $(".generate-barcode").each(function() {
            const productId = $(this).data("product-id");
            const barcode = String(productId).padStart(8, "0");
            $(this).text(barcode);
        });
        
        // Фокус на поле штрихкоду при завантаженні сторінки
        $("#barcodeInput").focus();
    });
</script>';
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

<!-- Сканер штрихкодів -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="m-0"><i class="fas fa-barcode me-2"></i>Пошук по штрихкоду</h5>
            </div>
            <div class="card-body">
                <div class="scanner-status" id="scannerStatus"></div>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                            <input type="text" class="form-control barcode-input" id="barcodeInput" 
                                   placeholder="Відскануйте або введіть штрихкод (8 цифр)" maxlength="8">
                            <button type="button" class="btn btn-primary" id="searchBarcodeBtn">
                                <i class="fas fa-search"></i> Знайти
                            </button>
                        </div>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Штрихкод формується з ID товару (8 цифр). Приклад: товар #123 = штрихкод 00000123
                        </small>
                    </div>
                    <div class="col-md-4 text-end">
                        <button type="button" class="btn btn-outline-secondary" id="clearSearchBtn">
                            <i class="fas fa-times"></i> Очистити
                        </button>
                    </div>
                </div>
                
                <!-- Результат пошуку -->
                <div class="found-product mt-3" id="foundProduct" style="display: none;"></div>
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
                                    <th>Залишок</th>
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
            <div class="modal-header">
                <h5 class="modal-title" id="barcodeInfoModalLabel">
                    <i class="fas fa-barcode me-2"></i>Інформація про штрихкоди
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <h6>Як працюють штрихкоди в системі:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Штрихкод формується з ID товару</li>
                            <li><i class="fas fa-check text-success me-2"></i>Довжина штрихкоду: 8 цифр</li>
                            <li><i class="fas fa-check text-success me-2"></i>Приклад: товар #123 → штрихкод 00000123</li>
                            <li><i class="fas fa-check text-success me-2"></i>Підтримка сканерів штрихкодів</li>
                            <li><i class="fas fa-check text-success me-2"></i>Ручне введення штрихкоду</li>
                        </ul>
                        
                        <h6 class="mt-4">Приклади штрихкодів:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
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
                                        <td>Перший товар</td>
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
                        
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Порада:</strong> Для швидкого сканування використовуйте плаваючу кнопку 
                            <span class="badge bg-primary"><i class="fas fa-barcode"></i></span> 
                            в правому нижньому куті екрану.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрити</button>
            </div>
        </div>
    </div>
</div>

<!-- Додаткова кнопка інформації про штрихкоди -->
<div class="position-fixed" style="bottom: 90px; right: 20px; z-index: 1000;">
    <button type="button" class="btn btn-info btn-sm rounded-circle" data-bs-toggle="modal" 
            data-bs-target="#barcodeInfoModal" title="Інформація про штрихкоди">
        <i class="fas fa-question"></i>
    </button>
</div>