<?php
// app/controllers/HomeController.php - Контроллер для главной страницы

class HomeController extends BaseController {
    private $productModel;
    private $categoryModel;
    
    public function __construct() {
        parent::__construct();
        $this->productModel = new Product();
        $this->categoryModel = new Category();
    }
    
    /**
     * Отображение главной страницы
     */
    public function index() {
        // Получение рекомендуемых продуктов
        $featuredProducts = $this->productModel->getFeatured(6);
        
        // Получение категорий для меню
        $categories = $this->categoryModel->getForMenu(5);
        
        // Получение новых продуктов
        $newProducts = $this->productModel->getFiltered(1, 8, [
            'is_active' => 1,
            'sort' => 'newest'
        ])['items'];
        
        // Передача данных в представление
        $this->data['featuredProducts'] = $featuredProducts;
        $this->data['categories'] = $categories;
        $this->data['newProducts'] = $newProducts;
        
        $this->view('home/index');
    }
}