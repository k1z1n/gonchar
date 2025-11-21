<?php

$errors = [];

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $surname = $_POST['surname'];
    $username = $_POST['username'];
    $patronymic = $_POST['patronymic'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $re_password = $_POST['re_password'];

    if(empty($surname) || empty($username) || empty($email) || empty($password) || empty($re_password)) {
        $errors[] = 'Заполните пустые поля';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'E-mail не валиден';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Пароль должен быть не менее 6 символов';
    } elseif ($password != $re_password) {
        $errors[] = 'Пароли не совпадают';
    }

    $sql = ("SELECT * FROM users WHERE email = ?");
    $stmt = $database->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch(2);

    if($user) {
        $errors[] = 'Пользователь существует';
    }

    if(empty($errors)) {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $sql = ("INSERT INTO users (surname, username, patronymic, email, password) VALUES (?, ?, ?, ?, ?)");
        $stmt = $database->prepare($sql);
        $stmt->execute([$surname, $username, $patronymic, $email, $password]);
        echo "<script>document.location.href='./?page=login';</script>";
    }
}




?>


<div class="avtoreg_block mt-105">
    <img src="assets/media/images/register/img.svg" alt="">
    <form method="post" class="avtoreg_form container">
        <h1>РЕГИСТРАЦИЯ</h1>
        <?php if(!empty($errors)): ?>
            <div class="errors-container">
                <?php foreach($errors as $error): ?>
                    <p><?=$error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <input type="text" placeholder="Фамилия*" name="surname" value="<?=$_POST['surname'] ?? ''?>">
        <input type="text" placeholder="Имя*" name="username" value="<?=$_POST['username'] ?? ''?>">
        <input type="text" placeholder="Отчество" name="patronymic" value="<?=$_POST['patronymic'] ?? ''?>">
        <input type="email" id="" placeholder="E-mail*" name="email" value="<?=$_POST['email'] ?? ''?>">
        <input type="password" id="" placeholder="Придумайте пароль*" name="password">
        <input type="password" id="" placeholder="Повторите пароль*" name="re_password">
        <p>Нажимая кнопку «Регистрация», вы даете согласие на обработку ваших персональных данных в соответствии с
            Политикой конфиденциальности.</p>
        <button class="btn_bg">СОЗДАТЬ АККАУНТ</button>
        <a href="./?page=login">Есть аккаунт?</a>
    </form>
</div>