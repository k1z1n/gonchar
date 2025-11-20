<div class="profile_navigation">
    <div class="profile_nav_wrapper">
        <?php if(isset($_SESSION['user_id'])): ?>
            <?php if($USER['role'] == 'admin'): ?>
                <a href="./?page=admin_users">ПАНЕЛЬ АДМИНИСТРАТОРА</a>
            <?php endif; ?>
        <?php endif; ?>

        <a href="./?page=profile">НАЙСТРОКИ АККАУНТА</a>
        <a href="./?page=user_orders">МОИ ЗАКАЗЫ</a>
        <a href="./?page=cart">КОРЗИНА</a>

    </div>
</div>