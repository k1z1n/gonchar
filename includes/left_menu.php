<div class="profile_navigation">
    <div class="profile_nav_wrapper">
        <?php if($USER['role'] === 'admin'): ?>
        <a href="./?page=admin_users">ПАНЕЛЬ АДМИНИСТРАТОРА</a>
        <a href="./?page=profile">НАЙСТРОКИ АККАУНТА</a>
        <a href="./?page=user_orders">МОИ ЗАКАЗЫ</a>
        <a href="">МОИ ЗАПИСИ</a>
        <a href="./?page=basket">КОРЗИНА</a>
        <?php endif; ?>
    </div>
</div>