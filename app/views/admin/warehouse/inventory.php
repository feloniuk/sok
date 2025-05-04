<?php
// app/views/admin/warehouse/inventory.php
$title = 'Інвентаризація';

// Функции для фильтрации и пагинации
function buildFilterUrl($newParams = []) {
    $currentParams = $_GET;
    $params = array_merge($currentParams, $newParams);
    
    // Удаление пустых параметров
    foreach ($params as $key => $value) {
        if ($value === '' || $value === null) {
            unset($params[$key]);
        }
    }
    
    return '?' . http_build_query($params);
}

// Дополнительные CSS стили
$extra_css = '
<style>
    .filter-card {
        border: none;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .inventory-table img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
    }
    
    .stock-badge-success {
        background-color: #28a745;
    }
    
    .stock-badge-warning {
        background-color: #ffc107;
        color: #212529;
    }
    
    .stock-badge-danger {
        background-color: #dc3545;
    }
</style>';

// Дополнительные JS скрипты
$extra_js = '
<script>
$(document).ready(function() {
    // Инициализация выбора диапазона количества
    $("#stock-range").slider({
        range: true,
        min: 0,
        max: 500,
        values: [' . ($_GET['min_stock'] ?? 0) . ', ' . ($_GET['max_stock'] ?? 500) . '],
        slide: function(event, ui) {
            $("#min_stock").val(ui.values[0]);
            $("#max_stock").val(ui.values[1]);
            $("#stock-range-text").text(ui.values[0] + " шт. - " + ui.values[1] + " шт.");
        }
    });
    
    // Фильтрация при изменении параметров
    $(".filter-control").on("change", function() {
        $("#filterForm").submit();
    });
    
    // Сброс фильтров
    $("#resetFilters").on("click", function() {
        $(".filter-control").each(function() {
            if ($(this).is("select")) {
                $(this).val("");
            } else {
                $(this).val("");
            }
        });
        $("#min_stock").val(0);
        $("#max_stock").val(500);
        $("#stock-range").slider("values", [0, 500]);
        $("#stock-range-text").text("0 шт. - 500 шт.");
        $("#filterForm").submit();
    });
    
    // Обновление запасов (quick edit)
    $(".edit-stock").on("click", function() {
        const productId = $(this).data("id");
        const currentStock = $(this).data("stock");
        
        $("#editProductId").val(productId);
        $("#editCurrentStock").text(currentStock);
        $("#editNewStock").val(currentStock);
        
        $("#editStockModal").modal("show");
    });
    
    // Расчет разницы в количестве
    $("#editNewStock").on("input", function() {
        const currentStock = parseInt($("#editCurrentStock").text());
        const newStock = parseInt($(this).val()) || 0;
        const difference = newStock - currentStock;
        
        $("#editStockDifference").text(difference);
        
        if (difference > 0) {
            $("#editStockDifference").removeClass("text-danger").addClass("text-success");
            $("#differenceLabel").text("Надходження:");
        } else if (difference < 0) {
            $("#editStockDifference").removeClass("text-success").addClass("text-danger");
            $("#differenceLabel").text("Витрата:");
        } else {
            $("#editStockDifference").removeClass("text-success text-danger");
            $("#differenceLabel").text("Різниця:");
        }
    });
});
</script>';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h2 mb-0"><?= $title ?></h1>
        <p class="text-muted">Управління запасами товарів на складі</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="<?= base_url('admin/warehouse/add_movement') ?>" class="btn btn-success">
            <i class="fas fa-plus-circle me-1"></i> Додати рух
        </a>
    </div>
</div>

<div class="row">
    <!-- Фильтры -->
    <div class="col-md-3 mb-4">
        <div class="card filter-card">
            <div class="card-header bg-primary text-white">
                <h5 class="m-0">Фільтри</h5>
            </div>
            <div class="card-body">
                <form id="filterForm" action="<?= base_url('admin/warehouse/inventory') ?>" method="GET">
                    <!-- Поиск по ключевому слову -->
                    <div class="mb-3">
                        <label for="keyword" class="form-label">Пошук</label>
                        <input type="text" class="form-control filter-control" id="keyword" name="keyword" value="<?= $_GET['keyword'] ?? '' ?>" placeholder="Назва продукту...">
                    </div>
                    
                    <!-- Категория -->
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Категорія</label>
                        <select class="form-select filter-control" id="category