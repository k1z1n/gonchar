<?php
$currentUserRole = $USER['role'] ?? null;

if ($currentUserRole === null && isset($user['role'])) {
    $currentUserRole = $user['role'];
}

if ($currentUserRole === null && isset($_SESSION['user_id']) && isset($database)) {
    $roleStmt = $database->prepare("SELECT role FROM users WHERE id = ?");
    $roleStmt->execute([$_SESSION['user_id']]);
    $roleResult = $roleStmt->fetch(PDO::FETCH_ASSOC);
    $currentUserRole = $roleResult['role'] ?? null;
}
?>

<div class="profile_navigation">
    <div class="profile_nav_wrapper">
        <?php if(isset($_SESSION['user_id'])): ?>
            <?php if($currentUserRole === 'admin'): ?>
                <a href="./?page=admin_users">ПАНЕЛЬ АДМИНИСТРАТОРА</a>
            <?php endif; ?>
        <?php endif; ?>

        <a href="./?page=profile">НАЙСТРОКИ АККАУНТА</a>
        <a href="./?page=user_orders">МОИ ЗАКАЗЫ</a>
        <a href="./?page=cart">КОРЗИНА</a>

    </div>
</div>