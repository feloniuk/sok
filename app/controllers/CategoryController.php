<?php
// app/controllers/CategoryController.php - Контроллер для работы с категориями

class CategoryController extends BaseController {
    private $categoryModel;
    private $productModel;
    
    public function __construct() {
        parent::__construct();
        $this->categoryModel = new Category();
        $this->productModel = new Product();
    }
    
    /**
     * Отображение списка категорий
     */
    public function index() {
        // Получение всех категорий с количеством продуктов
        $categories = $this->categoryModel->getAllWithProductCount();
        
        // Передача данных в представление
        $this->data['categories'] = $categories;
        
        $this->view('categories/index');
    }
    
    /**
     * Отображение категории и ее продуктов
     *
     * @param int $id
     */
    public function details($id, $data = []) {
        // Получение страницы для пагинации
        $page = intval($this->input('page', 1));
        
        // Получение категории с продуктами
        $category = $this->categoryModel->getWithProducts($id, $page);
        
        if (!$category) {
            $this->setFlash('error', 'Категория не найдена.');
            $this->redirect('categories');
            return;
        }
        
        // Передача данных в представление
        $this->data['category'] = $category;
        
        $this->view('categories/view');
    }
    
    /**
     * Отображение формы создания категории
     */
    public function create() {
        // Проверка прав доступа
        if (!has_role('admin')) {
            $this->setFlash('error', 'У вас нет доступа к этой странице.');
            $this->redirect('categories');
            return;
        }
        
        $this->view('categories/form');
    }
    
    /**
     * Обработка формы создания категории
     */
    public function store() {
        // Проверка прав доступа
        if (!has_role('admin')) {
            $this->setFlash('error', 'У вас нет доступа к этой странице.');
            $this->redirect('categories');
            return;
        }
        
        // Проверка метода запроса
        if (!$this->isPost()) {
            $this->redirect('categories/create');
            return;
        }
        
        // Проверка CSRF-токена
        $this->validateCsrfToken();
        
        // Получение данных из формы
        $name = $this->input('name');
        $description = $this->input('description');
        
        // Валидация данных
        $errors = [];
        
        if (empty($name)) {
            $errors['name'] = 'Введите название категории';
        }
        
        // Обработка загруженного изображения
        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = upload_file('image', 'categories');
            
            if ($image === null) {
                $errors['image'] = 'Ошибка при загрузке изображения. Проверьте формат и размер файла.';
            }
        }
        
        // Если есть ошибки, возвращаемся к форме
        if (!empty($errors)) {
            set_form_errors($errors);
            $this->redirect('categories/create');
            return;
        }
        
        // Создание новой категории
        $categoryData = [
            'name' => $name,
            'description' => $description,
            'image' => $image
        ];
        
        $categoryId = $this->categoryModel->create($categoryData);
        
        if ($categoryId) {
            $this->setFlash('success', 'Категория успешно создана.');
            $this->redirect('categories');
        } else {
            $this->setFlash('error', 'Ошибка при создании категории.');
            $this->redirect('categories/create');
        }
    }
    
    /**
     * Отображение формы редактирования категории
     *
     * @param int $id
     */
    public function edit($id) {
        // Проверка прав доступа
        if (!has_role('admin')) {
            $this->setFlash('error', 'У вас нет доступа к этой странице.');
            $this->redirect('categories');
            return;
        }
        
        // Получение категории
        $category = $this->categoryModel->getById($id);
        
        if (!$category) {
            $this->setFlash('error', 'Категория не найдена.');
            $this->redirect('categories');
            return;
        }
        
        // Передача данных в представление
        $this->data['category'] = $category;
        
        $this->view('categories/form');
    }
    
    /**
     * Обработка формы редактирования категории
     *
     * @param int $id
     */
    public function update($id) {
        // Проверка прав доступа
        if (!has_role('admin')) {
            $this->setFlash('error', 'У вас нет доступа к этой странице.');
            $this->redirect('categories');
            return;
        }
        
        // Проверка метода запроса
        if (!$this->isPost()) {
            $this->redirect('categories/edit/' . $id);
            return;
        }
        
        // Проверка CSRF-токена
        $this->validateCsrfToken();
        
        // Получение категории
        $category = $this->categoryModel->getById($id);
        
        if (!$category) {
            $this->setFlash('error', 'Категория не найдена.');
            $this->redirect('categories');
            return;
        }
        
        // Получение данных из формы
        $name = $this->input('name');
        $description = $this->input('description');
        
        // Валидация данных
        $errors = [];
        
        if (empty($name)) {
            $errors['name'] = 'Введите название категории';
        }
        
        // Обработка загруженного изображения
        $image = $category['image']; // По умолчанию используем текущее изображение
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $newImage = upload_file('image', 'categories');
            
            if ($newImage === null) {
                $errors['image'] = 'Ошибка при загрузке изображения. Проверьте формат и размер файла.';
            } else {
                $image = $newImage;
            }
        }
        
        // Если есть ошибки, возвращаемся к форме
        if (!empty($errors)) {
            set_form_errors($errors);
            $this->redirect('categories/edit/' . $id);
            return;
        }
        
        // Обновление категории
        $categoryData = [
            'name' => $name,
            'description' => $description,
            'image' => $image
        ];
        
        $result = $this->categoryModel->update($id, $categoryData);
        
        if ($result) {
            $this->setFlash('success', 'Категория успешно обновлена.');
            $this->redirect('categories');
        } else {
            $this->setFlash('error', 'Ошибка при обновлении категории.');
            $this->redirect('categories/edit/' . $id);
        }
    }
    
    /**
     * Удаление категории
     *
     * @param int $id
     */
    public function delete($id) {
        // Проверка прав доступа
        if (!has_role('admin')) {
            $this->setFlash('error', 'У вас нет доступа к этой странице.');
            $this->redirect('categories');
            return;
        }
        
        // Получение категории
        $category = $this->categoryModel->getById($id);
        
        if (!$category) {
            $this->setFlash('error', 'Категория не найдена.');
            $this->redirect('categories');
            return;
        }
        
        // Проверка, есть ли продукты в категории
        if ($this->categoryModel->hasProducts($id)) {
            $this->setFlash('error', 'Невозможно удалить категорию, содержащую продукты.');
            $this->redirect('categories');
            return;
        }
        
        // Удаление категории
        $result = $this->categoryModel->delete($id);
        
        if ($result) {
            $this->setFlash('success', 'Категория успешно удалена.');
        } else {
            $this->setFlash('error', 'Ошибка при удалении категории.');
        }
        
        $this->redirect('categories');
    }
}