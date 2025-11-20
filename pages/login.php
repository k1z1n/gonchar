<?php

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $errors[] = 'Заполните пустые поля';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'E-mail введен не корректно';
    }

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch(2);

    if (!$user || !password_verify($password, $user["password"])) {
        $errors[] = "Неверный логин или пароль";
    } elseif ($user['status'] === 1) {
        $errors[] = "Вы заблокированы.";
    } elseif (empty($errors)) {
        $_SESSION['user_id'] = $user['id'];
        if ($user['role'] == 'admin') {
            header("Location: ./?page=admin_users");
        } else {
            header('Location: ./?page=profile');
        }
    }
}


?>



<div class="avtoreg_block mt-105">
    <img src="assets/media/images/register/img.svg" alt="">
    <form class="avtoreg_form container" method="post">
        <h1>ВОЙТИ В АККАУНТ</h1>
        <?php if (!empty($errors)): ?>
            <div class="errors-container">
                <?php foreach ($errors as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <input type="email" placeholder="E-mail*" name="email">
        <input type="password" placeholder="Введите пароль*" name="password">
        <div class="links_login">
            <a href="">Забыли пароль?</a>
            <a href="./?page=register">Нет аккаунта?</a>
        </div>
        <button class="btn_bg">ВОЙТИ</button>
    </form>
</div>