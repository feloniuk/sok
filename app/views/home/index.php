<?php
// app/views/home/index.php - Главная страница
$title = 'Ласкаво просимо';

// Подключение дополнительных CSS для слайдера
$extra_css = '
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">
<style>
    .hero-section {
        background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url("' . asset_url('images/hero-bg.jpg') . '") no-repeat center center;
        background-size: cover;
        color: white;
        padding: 100px 0;
        margin-bottom: 40px;
        border-radius: 0.5rem;
    }
    .product-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    .feature-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: #007bff;
    }
    .slick-prev:before, .slick-next:before {
        color: #007bff;
    }
    .category-card {
        height: 200px;
        background-size: cover;
        background-position: center;
        display: flex;
        align-items: flex-end;
        border-radius: 0.5rem;
        overflow: hidden;
        position: relative;
    }
    .category-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(transparent 50%, rgba(0, 0, 0, 0.7));
    }
    .category-title {
        width: 100%;
        color: white;
        padding: 15px;
        position: relative;
        z-index: 1;
        font-weight: bold;
    }
</style>';

// Подключение дополнительных JavaScript для слайдера
$extra_js = '
<script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
<script>
    $(document).ready(function(){
        $(".featured-products").slick({
            dots: true,
            infinite: true,
            speed: 300,
            slidesToShow: 4,
            slidesToScroll: 1,
            autoplay: true,
            autoplaySpeed: 3000,
            responsive: [
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 3
                    }
                },
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: 2
                    }
                },
                {
                    breakpoint: 576,
                    settings: {
                        slidesToShow: 1
                    }
                }
            ]
        });
    });
</script>';
?>

<!-- Hero секция -->
<section class="hero-section">
    <div class="container text-center">
        <h1 class="display-4 mb-4">Свіжі соки для здорового життя</h1>
        <p class="lead mb-5">Найкращі натуральні соки від перевірених виробників за вигідними цінами</p>
        <div class="d-flex justify-content-center">
            <a href="<?= base_url('products') ?>" class="btn btn-primary btn-lg me-3">
                <i class="fas fa-shopping-cart me-2"></i> Переглянути продукти
            </a>
            <?php if (!is_logged_in()): ?>
                <a href="<?= base_url('auth/register') ?>" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-user-plus me-2"></i> Зареєструватися
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Категории -->
<section class="mb-5">
    <h2 class="text-center mb-4">Категорії соків</h2>
    <div class="row g-4">
        <?php foreach ($categories as $category): ?>
            
            <div class="col-md-4">
                <a href="<?= base_url('categories/view/' . $category['id']) ?>) ?>" class="text-decoration-none">
                    <div class="category-card" style="background-image: url('<?= $category['image'] ? upload_url($category['image']) : asset_url('images/no-image.jpg') ?>');">
                        <div class="category-title"><?= $category['name'] ?></div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Рекомендуемые продукты -->
<section class="mb-5">
    <h2 class="text-center mb-4">Рекомендовані продукти</h2>
    <div class="featured-products">
        <?php foreach ($featuredProducts as $product): ?>
            <div class="px-2">
                <div class="card product-card h-100">
                    <div class="position-relative">
                        <?php if ($product['is_featured']): ?>
                            <span class="position-absolute top-0 end-0 badge bg-warning m-2">Хіт продажу</span>
                        <?php endif; ?>
                        <img src="<?= $product['image'] ? upload_url($product['image']) : asset_url('images/no-image.jpg') ?>" class="card-img-top" alt="<?= $product['name'] ?>">
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= $product['name'] ?></h5>
                        <p class="card-text text-muted"><?= mb_substr($product['description'], 0, 60) ?>...</p>
                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <span class="fs-5 fw-bold text-primary"><?= number_format($product['price'], 2) ?> грн.</span>
                            <a href="<?= base_url('products/view/' . $product['id']) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye me-1"></i> Деталі
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Преимущества -->
<section class="mb-5">
    <h2 class="text-center mb-4">Чому обирають нас</h2>
    <div class="row g-4">
        <div class="col-md-4 text-center">
            <div class="feature-icon">
                <i class="fas fa-leaf"></i>
            </div>
            <h4>Натуральність</h4>
            <p>Всі наші соки виготовлені з натуральних інгредієнтів без консервантів та штучних добавок.</p>
        </div>
        <div class="col-md-4 text-center">
            <div class="feature-icon">
                <i class="fas fa-truck"></i>
            </div>
            <h4>Швидка доставка</h4>
            <p>Доставимо ваше замовлення протягом 24 годин у будь-який регіон України.</p>
        </div>
        <div class="col-md-4 text-center">
            <div class="feature-icon">
                <i class="fas fa-medal"></i>
            </div>
            <h4>Гарантія якості</h4>
            <p>Ми гарантуємо якість всіх наших продуктів та надаємо можливість повернення у разі незадоволення.</p>
        </div>
    </div>
</section>

<!-- Про нас -->
<section class="mb-5">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h2 class="mb-3">Про нашу компанію</h2>
            <p>Наша компанія спеціалізується на виробництві та продажу високоякісних натуральних соків. Ми працюємо з 2010 року і за цей час здобули довіру тисяч клієнтів.</p>
            <p>Ми обираємо тільки найкращі фрукти та овочі для виробництва наших соків, щоб ви могли насолоджуватися справжнім смаком та отримувати максимум корисних речовин.</p>
            <a href="#" class="btn btn-primary">Дізнатися більше</a>
        </div>
        <div class="col-md-6">
            <img src="<?= asset_url('images/about-us.jpg') ?>" alt="Про нас" class="img-fluid rounded shadow">
        </div>
    </div>
</section>

<!-- Отзывы клиентов -->
<section class="mb-5 bg-light py-4 rounded">
    <h2 class="text-center mb-4">Відгуки наших клієнтів</h2>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="mb-3 text-warning">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="card-text">«Чудові продукти! Апельсиновий сік просто неймовірний - свіжий та натуральний смак. Замовляю вже втретє і завжди задоволена якістю.»</p>
                    <div class="d-flex align-items-center">
                        <img src="<?= asset_url('images/user-profile.png') ?>" alt="Олена" class="rounded-circle me-3" width="50" height="50">
                        <div>
                            <h6 class="card-title mb-0">Олена Петренко</h6>
                            <small class="text-muted">Київ</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="mb-3 text-warning">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="card-text">«Дуже задоволений сервісом! Замовляв набір детокс-напоїв, доставка була вчасною, все гарно запаковано. Напої допомогли мені відновити енергію.»</p>
                    <div class="d-flex align-items-center">
                        <img src="<?= asset_url('images/user-profile.png') ?>" alt="Максим" class="rounded-circle me-3" width="50" height="50">
                        <div>
                            <h6 class="card-title mb-0">Максим Коваленко</h6>
                            <small class="text-muted">Львів</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="mb-3 text-warning">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <p class="card-text">«Регулярно замовляю смузі для всієї родини. Діти в захваті від ягідного смузі, а я віддаю перевагу зеленому. Якість на висоті, рекомендую!»</p>
                    <div class="d-flex align-items-center">
                        <img src="<?= asset_url('images/user-profile.png') ?>" alt="Ірина" class="rounded-circle me-3" width="50" height="50">
                        <div>
                            <h6 class="card-title mb-0">Ірина Сидоренко</h6>
                            <small class="text-muted">Одеса</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="text-center p-5 bg-primary text-white rounded">
    <h2 class="mb-3">Готові спробувати наші соки?</h2>
    <p class="lead mb-4">Зареєструйтеся зараз та отримайте знижку 10% на перше замовлення!</p>
    <a href="<?= base_url('auth/register') ?>" class="btn btn-lg btn-light">
        <i class="fas fa-user-plus me-2"></i> Зареєструватися зараз
    </a>
</section>