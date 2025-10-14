<?php

$errors = [];

$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $database->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(2);

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $surname = $_POST['surname'];
    $username = $_POST['username'];
    $patronymic = $_POST['patronymic'];
    $phone = $_POST['phone'];

    if(empty($surname) || empty($username)) {
        $errors[] = 'Заполните пустые поля';
    }

    if(empty($errors)) {
        $sql = "UPDATE users SET surname = ?, username = ?, patronymic = ?, phone = ? WHERE id = ?";
        $stmt = $database->prepare($sql);
        $stmt->execute([$surname, $username, $patronymic, $phone, $_SESSION['user_id']]);
        header('Location: ./?page=profile');
    }

}

?>



<section class="catalog_hero">
    <h1>ДОБРО ПОЖАЛОВАТЬ, <?=$USER['username'] ?></h1>
</section>

<!-- PROFILE CONTENT START -->
<div class="profile_content container">
    <div class="profile_layout">
        <!-- PROFILE NAVIGATION START -->
        <?php include('./includes/left_menu.php') ?>
        <!-- PROFILE NAVIGATION END -->


        <!-- PROFILE FORM START -->
        <form class="avtoreg_form container" method="post">
            <input type="text" placeholder="Фамилия*" name="surname" value="<?=$user['surname'] ?? '' ?>">
            <input type="text" placeholder="Имя*" name="username" value="<?=$user['username'] ?? '' ?>">
            <input type="text" placeholder="Отчество" name="patronymic" value="<?=$user['patronymic'] ?? '' ?>">
            <input type="tel" placeholder="Телефон" name="phone" value="<?=$user['phone'] ?? '' ?>">
            <input type="email" placeholder="E-mail*" disabled value="<?=$user['email'] ?? '' ?>">
            <a href="?exit">Выйти из аккаунта</a>
            <div>
                <?php if (!empty($errors)): ?>
                    <?php foreach ($errors as $error): ?>
                        <p><?= $error ?></p>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <button class="btn_bg">СОХРАНИТЬ ИЗМЕНЕНИЯ</button>
        </form>

        <!-- PROFILE FORM END -->
    </div>
</div>
<!-- PROFILE CONTENT END -->