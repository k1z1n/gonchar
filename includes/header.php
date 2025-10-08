<!-- HEADER START -->
<header>
    <div class="header_menu container">
        <div class="logo">
            <a href="./"><img src="assets/media/images/logo/logo.svg" alt=""></a>
        </div>
        <nav>
            <a href="">О НАС</a>
            <a href="">МАСТЕРКЛАССЫ</a>
            <a href="">КУПИТЬ ИЗДЕЛИЕ</a>
            <a href="">ЗАКАЗАТЬ ИЗДЕЛИЕ</a>
            <a href="">FAQ</a>
        </nav>
        <div class="icons_in_header">
            <img src="assets/media/images/index/icon1.svg" alt="">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="./?page=profile"><img src="assets/media/images/index/profile.svg" alt=""></a>
            <?php else: ?>
                <a href="./?page=register"><img src="assets/media/images/index/profile.svg" alt=""></a>
            <?php endif; ?>
            <img src="assets/media/images/index/basket.svg" alt="">
        </div>
    </div>
</header>
<!-- HEADER END -->