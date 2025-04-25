// main.js - Основні скрипти для сайту
// Глобальна змінна для базового URL сайту
const baseUrl = document.querySelector('meta[name="base-url"]') ? 
    document.querySelector('meta[name="base-url"]').getAttribute('content') : '/';

$(document).ready(function() {
    // Ініціалізація підказок Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Ініціалізація спливаючих підказок
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Автоматичне закриття сповіщень через 5 секунд
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);

    // Підтвердження видалення
    $('.confirm-delete').on('click', function(e) {
        e.preventDefault();
        var targetUrl = $(this).attr('href');
        var itemName = $(this).data('item-name') || 'цей запис';

        if (confirm('Ви впевнені, що хочете видалити ' + itemName + '?')) {
            window.location.href = targetUrl;
        }
    });

    // Перевірка форми перед відправкою
    $('form.needs-validation').on('submit', function(event) {
        if (!this.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        $(this).addClass('was-validated');
    });

    // Динамічне додавання товарів до замовлення
    if ($('#orderItemsContainer').length) {
        $('#addProductBtn').on('click', function() {
            var itemTemplate = $('#orderItemTemplate').html();
            var index = $('#orderItemsContainer .order-item').length;
            
            // Заміна індексів
            itemTemplate = itemTemplate.replace(/\{index\}/g, index);
            
            $('#orderItemsContainer').append(itemTemplate);
            initProductAutocomplete($('#product_' + index));
        });

        // Видалення товару із замовлення
        $(document).on('click', '.remove-item', function() {
            $(this).closest('.order-item').remove();
            updateTotalAmount();
        });

        // Оновлення суми при зміні кількості або ціни
        $(document).on('change', '.item-quantity, .item-price', function() {
            updateItemAmount($(this).closest('.order-item'));
            updateTotalAmount();
        });

        // Ініціалізація автозаповнення для вибору продуктів
        function initProductAutocomplete(element) {
            element.autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: baseUrl + "orders/products_json",
                        dataType: "json",
                        data: {
                            term: request.term
                        },
                        success: function(data) {
                            response(data);
                        }
                    });
                },
                minLength: 2,
                select: function(event, ui) {
                    var item = $(this).closest('.order-item');
                    item.find('.product-id').val(ui.item.id);
                    item.find('.item-price').val(ui.item.price);
                    item.find('.product-stock').text('В наявності: ' + ui.item.stock);
                    
                    updateItemAmount(item);
                    updateTotalAmount();
                }
            });
        }

        // Розрахунок вартості для окремого товару
        function updateItemAmount(item) {
            var quantity = parseFloat(item.find('.item-quantity').val()) || 0;
            var price = parseFloat(item.find('.item-price').val()) || 0;
            var amount = quantity * price;
            
            item.find('.item-amount').text(amount.toFixed(2) + ' грн.');
        }

        // Розрахунок загальної вартості замовлення
        function updateTotalAmount() {
            var total = 0;
            $('.order-item').each(function() {
                var quantity = parseFloat($(this).find('.item-quantity').val()) || 0;
                var price = parseFloat($(this).find('.item-price').val()) || 0;
                total += quantity * price;
            });
            
            $('#totalAmount').text(total.toFixed(2) + ' грн.');
            $('#totalAmountInput').val(total.toFixed(2));
        }

        // Ініціалізація автозаповнення для існуючих товарів
        $('.product-input').each(function() {
            initProductAutocomplete($(this));
        });
    }

    // Функціональність для сторінки продуктів
    if ($('.product-filter').length) {
        // Фільтрація продуктів при зміні параметрів
        $('.filter-control').on('change', function() {
            $('#filterForm').submit();
        });

        // Скидання фільтрів
        $('#resetFilters').on('click', function() {
            $('.filter-control').each(function() {
                if ($(this).is('select')) {
                    $(this).val('');
                } else if ($(this).is('input[type="checkbox"]')) {
                    $(this).prop('checked', false);
                } else {
                    $(this).val('');
                }
            });
            $('#filterForm').submit();
        });
    }

    // Функціональність для сторінки складу
    if ($('.warehouse-management').length) {
        // Зміна кількості при операціях руху товарів
        $('#movementType').on('change', function() {
            var type = $(this).val();
            if (type === 'outgoing') {
                $('#quantityLabel').text('Кількість (від\'ємне значення):');
                if ($('#quantity').val() > 0) {
                    $('#quantity').val(-$('#quantity').val());
                }
            } else {
                $('#quantityLabel').text('Кількість:');
                if ($('#quantity').val() < 0) {
                    $('#quantity').val(Math.abs($('#quantity').val()));
                }
            }
        });

        // Перевірка доступної кількості при відвантаженні
        $('#productId').on('change', function() {
            var productId = $(this).val();
            if (productId) {
                $.ajax({
                    url: baseUrl + 'warehouse/get_product_stock',
                    type: 'GET',
                    data: {product_id: productId},
                    dataType: 'json',
                    success: function(response) {
                        if (response.stock) {
                            $('#availableStock').text('Доступно на складі: ' + response.stock);
                            $('#maxQuantity').val(response.stock);
                        }
                    }
                });
            }
        });
    }

    // Функціональність для графіків на панелі керування
    if ($('#salesChart').length) {
        loadChartData();
    }

    // Завантаження даних для графіків
    function loadChartData(type = 'sales', period = 'month') {
        $.ajax({
            url: baseUrl + 'dashboard/chart_data',
            type: 'GET',
            data: {type: type, period: period},
            dataType: 'json',
            success: function(data) {
                if (data) {
                    switch (type) {
                        case 'sales':
                            renderSalesChart(data);
                            break;
                        case 'orders':
                            renderOrdersChart(data);
                            break;
                        case 'products':
                            renderProductsChart(data);
                            break;
                        case 'categories':
                            renderCategoriesChart(data);
                            break;
                    }
                }
            }
        });
    }

    // Зміна періоду для графіків
    $('.chart-period').on('click', function(e) {
        e.preventDefault();
        var period = $(this).data('period');
        var type = $('#chartType').val();
        
        $('.chart-period').removeClass('active');
        $(this).addClass('active');
        
        loadChartData(type, period);
    });

    // Зміна типу графіка
    $('#chartType').on('change', function() {
        var type = $(this).val();
        var period = $('.chart-period.active').data('period');
        
        loadChartData(type, period);
    });

    // Відображення графіка продажів
    function renderSalesChart(data) {
        var ctx = document.getElementById('salesChart').getContext('2d');
        
        if (window.salesChart) {
            window.salesChart.destroy();
        }
        
        var labels = [];
        var revenues = [];
        var profits = [];
        
        data.forEach(function(item) {
            labels.push(item.day);
            revenues.push(item.revenue);
            profits.push(item.profit);
        });
        
        window.salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Виручка',
                        data: revenues,
                        borderColor: '#4e73df',
                        backgroundColor: 'rgba(78, 115, 223, 0.05)',
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'Прибуток',
                        data: profits,
                        borderColor: '#1cc88a',
                        backgroundColor: 'rgba(28, 200, 138, 0.05)',
                        fill: true,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Динаміка продажів'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Сума (грн)'
                        }
                    }
                }
            }
        });
    }
    
    // Відображення графіка замовлень
    function renderOrdersChart(data) {
        var ctx = document.getElementById('salesChart').getContext('2d');
        
        if (window.salesChart) {
            window.salesChart.destroy();
        }
        
        var labels = [];
        var counts = [];
        var amounts = [];
        
        data.forEach(function(item) {
            labels.push(item.day);
            counts.push(item.count);
            amounts.push(item.amount);
        });
        
        window.salesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Кількість замовлень',
                        data: counts,
                        backgroundColor: '#4e73df',
                        borderColor: '#3a64d8',
                        borderWidth: 1
                    },
                    {
                        label: 'Сума замовлень',
                        data: amounts,
                        backgroundColor: '#1cc88a',
                        borderColor: '#18a978',
                        borderWidth: 1,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Статистика замовлень'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Кількість замовлень'
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false
                        },
                        title: {
                            display: true,
                            text: 'Сума замовлень (грн)'
                        }
                    }
                }
            }
        });
    }
    
    // Відображення графіка топових продуктів
    function renderProductsChart(data) {
        var ctx = document.getElementById('salesChart').getContext('2d');
        
        if (window.salesChart) {
            window.salesChart.destroy();
        }
        
        var labels = [];
        var quantities = [];
        
        data.forEach(function(item) {
            labels.push(item.name);
            quantities.push(item.quantity);
        });
        
        window.salesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Кількість проданих одиниць',
                        data: quantities,
                        backgroundColor: '#36b9cc',
                        borderColor: '#2ea7b9',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    title: {
                        display: true,
                        text: 'Топ продуктів за продажами'
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Кількість одиниць'
                        }
                    }
                }
            }
        });
    }
    
    // Відображення графіка продажів за категоріями
    function renderCategoriesChart(data) {
        var ctx = document.getElementById('salesChart').getContext('2d');
        
        if (window.salesChart) {
            window.salesChart.destroy();
        }
        
        var labels = [];
        var revenues = [];
        var backgroundColors = [
            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', 
            '#5a5c69', '#6610f2', '#6f42c1', '#e83e8c', '#fd7e14'
        ];
        
        data.forEach(function(item, index) {
            labels.push(item.name);
            revenues.push(item.revenue);
        });
        
        window.salesChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [
                    {
                        data: revenues,
                        backgroundColor: backgroundColors,
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Продажі за категоріями'
                    },
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    }
    
    // Ініціалізація datepicker для полів з датами
    if ($('.datepicker').length) {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true,
            language: 'uk'
        });
    }
    
    // Відображення превью зображень перед завантаженням
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
        
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            var previewElement = $(this).data('preview');
            
            reader.onload = function(e) {
                $(previewElement).attr('src', e.target.result);
                $(previewElement).removeClass('d-none');
            }
            
            reader.readAsDataURL(this.files[0]);
        }
    });
    
    // Функціональність для створення замовлення
    if ($('.order-create-form').length) {
        // Оновлення списку доступних продуктів при зміні складу
        $('#warehouse_id').on('change', function() {
            var warehouseId = $(this).val();
            if (warehouseId) {
                $.ajax({
                    url: baseUrl + 'warehouse/get_available_products',
                    type: 'GET',
                    data: {warehouse_id: warehouseId},
                    dataType: 'json',
                    success: function(response) {
                        updateProductsDropdown(response);
                    }
                });
            }
        });
        
        // Функція оновлення випадаючого списку продуктів
        function updateProductsDropdown(products) {
            var dropdown = $('#product_id');
            dropdown.empty();
            
            if (products.length === 0) {
                dropdown.append($('<option></option>').attr('value', '').text('Немає доступних продуктів'));
                return;
            }
            
            dropdown.append($('<option></option>').attr('value', '').text('Виберіть продукт'));
            
            $.each(products, function(i, product) {
                dropdown.append($('<option></option>')
                    .attr('value', product.id)
                    .attr('data-price', product.price)
                    .attr('data-stock', product.stock_quantity)
                    .text(product.name + ' (' + product.price + ' грн.)')
                );
            });
        }
    }
});