<?php

if(isset($_SESSION['user_id'])) {
    if($USER['role'] !== 'admin') {
        header('Location: ./?page=login');
    }
}

$errors = [];

if(isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
} else {
    $errors[] = 'Категория не найдена';
}


$sql = "SELECT * FROM category WHERE id = ?";
$stmt = $database->prepare($sql);
$stmt->execute([$id]);
$category = $stmt->fetch(2);

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sql = "DELETE FROM category WHERE id = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$id]);
    header('Location: ./?page=admin_categories');
}

?>


<div class="admin-container container">
    <!-- Сайдбар -->
    <?php include_once('./includes/admin_menu.php'); ?>

    <!-- Основной контент -->
    <main class="admin-main-content">
        <div class="admin-header">
            <h2 class="admin-page-title">Удаление категории</h2>
            <a href="?exit">Выйти</a>
        </div>

        <div class="admin-form-container">
            <form method="post">
                <div class="admin-form-group">
                    <label>Название категории</label>
                    <input type="text" value="<?=$category['title'] ?>" disabled>
                </div>

                <div class="admin-form-group">
                    <label>Количество товаров в категории</label>
                    <input type="text" value="<?=$category['count'] ?>" disabled>
                </div>

                <div class="admin-form-group">
                    <p style="color: #d32f2f; font-weight: 600; font-size: 16px; margin-top: 20px;">
                        Вы точно желаете удалить данную категорию?
                    </p>
                    <?php if($category['count'] > 0): ?>
                        <p style="color: #f57c00; font-size: 14px; margin-top: 10px;">
                            Внимание! В этой категории находится <?= $category['count'] ?> товар(ов). При удалении товары данной категории удалятся.
                        </p>
                    <?php endif; ?>
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
                    <button type="submit" class="admin-btn admin-btn-primary" style="background-color: #d32f2f;">Удалить</button>
                </div>
            </form>
        </div>
    </main>
</div>
