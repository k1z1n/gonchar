<?php

$errors = [];

if(isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
} else {
    $errors[] = 'Категория не найдена';
}

$sql_categories = "SELECT * FROM category";
$stmt_categories = $database->prepare($sql_categories);
$stmt_categories->execute();
$categories = $stmt_categories->fetchAll(2);

$sql = "SELECT * FROM category WHERE id = ?";
$stmt = $database->prepare($sql);
$stmt->execute([$id]);
$category = $stmt->fetch(2);

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
        $sql = "UPDATE category SET title = ? WHERE id = ?";
        $stmt = $database->prepare($sql);
        $stmt->execute([$name_category, $id]);
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
            <h2 class="admin-page-title">Редактирование категории</h2>
            <a href="?exit">Выйти</a>
        </div>

        <div class="admin-form-container">
            <form id="categoryForm" method="post">
                <div class="admin-form-group">
                    <label for="categoryName">Название категории</label>
                    <input type="text" name="name_category" value="<?=$category['title'] ?? '' ?>" required>
                </div>
<!--                <div class="admin-form-group">-->
<!--                    <label for="categoryDescription">Описание</label>-->
<!--                    <textarea id="categoryDescription"></textarea>-->
<!--                </div>-->
                <div>
                    <?php if(!empty($errors)): ?>
                        <?php foreach($errors as $error): ?>
                            <p><?=$error ?></p>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="admin-form-actions">
                    <a href="./?page=admin_categories" class="admin-btn admin-btn-secondary">Отмена</a>
                    <button type="submit" class="admin-btn admin-btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </main>
</div>
