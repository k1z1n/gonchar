<?php

if(isset($_SESSION['user_id'])) {
    if($USER['role'] !== 'admin') {
        header('Location: ./?page=login');
    }
}

$errors = [];

$sql = "SELECT * FROM category";
$stmt = $database->prepare($sql);
$stmt->execute();
$categories = $stmt->fetchAll(2);

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name_category = $_POST['name_category'];

    if(empty($name_category)) {
        $errors[] = 'Заполните название категории';
    }
    foreach ($categories as $category) {
        if($name_category == $category['title']) {
            $errors[] = 'Данная категория уже существует';
        }
    }
    if(empty($errors)) {
        $sql = "INSERT INTO category (title) VALUES (?)";
        $stmt = $database->prepare($sql);
        $stmt->execute([$name_category]);
        header('Location: ./?page=admin_categories');

    }

}

?>


<div class="admin-container container">
    <!-- Сайдбар -->
    <?php include_once('./includes/admin_menu.php'); ?>

    <!-- Основной контент -->
    <main class="admin-main-content">
        <div class="admin-header">
            <h2 class="admin-page-title">Добавить категорию</h2>
            <a href="?exit">Выйти</a>
        </div>

        <div class="admin-form-container">
            <form id="categoryForm" method="post">
                <div class="admin-form-group">
                    <label for="categoryName">Название категории</label>
                    <input type="text" id="categoryName" name="name_category" >
                </div>
                <?php if(!empty($errors)): ?>
                    <div class="errors-container">
                        <?php foreach($errors as $error): ?>
                            <p><?=$error ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <div class="admin-form-actions">
                    <a href="./?page=admin_categories" class="admin-btn admin-btn-secondary">Отмена</a>
                    <button type="submit" class="admin-btn admin-btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </main>
</div>
