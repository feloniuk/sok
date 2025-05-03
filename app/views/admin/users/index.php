<?php
// app/views/admin/users/index.php - Users list page for admin
$title = 'Управління користувачами';

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

// Функція для перетворення ролей
function getRoleName($role) {
    $roleNames = [
        'admin' => 'Адміністратор',
        'sales_manager' => 'Менеджер продажів',
        'warehouse_manager' => 'Менеджер складу',
        'customer' => 'Клієнт'
    ];
    return $roleNames[$role] ?? $role;
}

function getRoleBadgeClass($role) {
    $roleClasses = [
        'admin' => 'danger',
        'sales_manager' => 'info',
        'warehouse_manager' => 'success',
        'customer' => 'primary'
    ];
    return $roleClasses[$role] ?? 'secondary';
}

// Підключення додаткових CSS
$extra_css = '
<style>
    .user-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-radius: 0.5rem;
        overflow: hidden;
        border: none;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .user-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .role-badge {
        font-size: 0.8rem;
    }
    
    .filter-card {
        border: none;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
</style>';

// Підключення додаткових JS
$extra_js = '
<script>
    $(document).ready(function() {
        // Фільтрація користувачів при зміні параметрів
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
    });
</script>';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h3 mb-0 text-gray-800"><?= $title ?></h1>
        <p class="text-muted">Загальна кількість користувачів: <?= $pagination['total_items'] ?? 0 ?></p>
    </div>
    <div class="col-md-4 text-end">
        <a href="<?= base_url('users/create') ?>" class="btn btn-success">
            <i class="fas fa-user-plus me-1"></i> Додати нового користувача
        </a>
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
                <form id="filterForm" action="<?= base_url('users') ?>" method="GET">
                    <!-- Пошук за ключовим словом -->
                    <div class="mb-3">
                        <label for="keyword" class="form-label">Пошук</label>
                        <input type="text" class="form-control filter-control" id="keyword" name="keyword" value="<?= $_GET['keyword'] ?? '' ?>" placeholder="Ім'я, email, телефон...">
                    </div>
                    
                    <!-- Роль -->
                    <div class="mb-3">
                        <label for="role" class="form-label">Роль</label>
                        <select class="form-select filter-control" id="role" name="role">
                            <option value="">Всі ролі</option>
                            <option value="admin" <?= isset($_GET['role']) && $_GET['role'] == 'admin' ? 'selected' : '' ?>>Адміністратор</option>
                            <option value="sales_manager" <?= isset($_GET['role']) && $_GET['role'] == 'sales_manager' ? 'selected' : '' ?>>Менеджер продажів</option>
                            <option value="warehouse_manager" <?= isset($_GET['role']) && $_GET['role'] == 'warehouse_manager' ? 'selected' : '' ?>>Менеджер складу</option>
                            <option value="customer" <?= isset($_GET['role']) && $_GET['role'] == 'customer' ? 'selected' : '' ?>>Клієнт</option>
                        </select>
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
    
    <!-- Список користувачів -->
    <div class="col-md-9">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Список користувачів</h6>
            </div>
            <div class="card-body">
                <?php if (empty($users)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Користувачі не знайдені. Спробуйте змінити параметри фільтрації.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Ім'я користувача</th>
                                    <th>Email</th>
                                    <th>ПІБ</th>
                                    <th>Роль</th>
                                    <th>Дата реєстрації</th>
                                    <th>Дії</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= $user['id'] ?></td>
                                        <td><?= $user['username'] ?></td>
                                        <td><?= $user['email'] ?></td>
                                        <td><?= $user['first_name'] . ' ' . $user['last_name'] ?></td>
                                        <td>
                                            <span class="badge bg-<?= getRoleBadgeClass($user['role']) ?>">
                                                <?= getRoleName($user['role']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= base_url('users/view/' . $user['id']) ?>" class="btn btn-outline-primary" title="Перегляд">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= base_url('users/edit/' . $user['id']) ?>" class="btn btn-outline-warning" title="Редагування">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php if ($user['id'] != get_current_user_id()): ?>
                                                    <a href="<?= base_url('users/delete/' . $user['id']) ?>" class="btn btn-outline-danger confirm-delete" data-item-name="користувача <?= $user['username'] ?>" title="Видалення">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                <?php endif; ?>
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