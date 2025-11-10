<?php

$sql_products = "SELECT * FROM products";
$stmt_products = $database->prepare($sql_products);
$stmt_products->execute();
$products = $stmt_products->fetchAll(2);



?>





<!-- BANNER START -->
<section id="about" class="banner container mt-105">
    <!-- Фоновые изображения -->
    <div class="banner_images">
        <div class="hero_image_left">
            <img src="assets/media/images/index/vaza1.svg" alt="Гончарная мастерская">
        </div>
        <div class="hero_image_right">
            <img src="assets/media/images/index/hero-image-1-1ff654.png" alt="Гончарные изделия">
        </div>
    </div>
    <!-- Основной контент -->
    <div class="banner_content">
        <h1 class="banner_title">
            <span class="title_line1">ГОНЧАРНАЯ</span>
            <span class="title_line2">СТУДИЯ</span>
            <span class="title_line3">ГОНЧАРОК</span>
        </h1>
        <p class="banner_subtitle">ШКОЛА КЕРАМИКИ И ГОНЧАРНОГО МАСТЕРСТВА</p>
        <button class="btn_bg">ЗАПИСАТЬСЯ</button>
    </div>
</section>
<!-- BANNER END -->

<div id="masterclasses" class="h2_title container mt-105">
    <h2>Мастер классы</h2>
    <hr>
</div>

<!-- MASTERKLASS START -->
<div class="parent_masterklass container">
    <div class="masterklass_block1">
        <div class="masterklass_block_info">
            <div class="macterklass_img_btn">
                <img src="assets/media/images/index/mk1.png" alt="">
                <button class="btn">ЗАПИСАТЬСЯ</button>
            </div>
            <h3>ГОНЧАРНЫЙ КРУГ</h3>
            <p>2 800 ₽</p>
        </div>
    </div>
    <div class="masterklass_block2">
        <div class="masterklass_block_info">
            <div class="macterklass_img_btn">
                <img src="assets/media/images/index/mk2.png" alt="">
                <button class="btn">ЗАПИСАТЬСЯ</button>
            </div>
            <h3>СВИДАНИЕ В ГОНЧАРНОЙ СТУДИИ</h3>
            <p>2 800 ₽</p>
        </div>
    </div>
    <div class="masterklass_block3">
        <div class="masterklass_block_info">
            <div class="macterklass_img_btn">
                <img src="assets/media/images/index/mk3.png" alt="">
                <button class="btn">ЗАПИСАТЬСЯ</button>
            </div>
            <h3>ЗНАКОМСТВО С ГЛИНОЙ</h3>
            <p>2 800 ₽</p>
        </div>
    </div>
    <div class="masterklass_block4">
        <div class="masterklass_block_info">
            <div class="macterklass_img_btn">
                <img src="assets/media/images/index/mk4.png" alt="">
                <button class="btn">ЗАПИСАТЬСЯ</button>
            </div>
            <h3>РУЧНАЯ ЛЕПКА</h3>
            <p>2 800 ₽</p>
        </div>
    </div>
    <div class="masterklass_block5">
        <div class="masterklass_block_info">
            <div class="macterklass_img_btn">
                <img src="assets/media/images/index/mk5.png" alt="">
                <button class="btn">ЗАПИСАТЬСЯ</button>
            </div>
            <h3>ИНДИВИДУАЛЬНЫЙ МАСТЕР КЛАСС</h3>
            <p>2 800 ₽</p>
        </div>
    </div>
</div>
<!-- MASTERKLASS END -->

<div id="catalog" class="h2_title container mt-105">
    <h2>Популярные товары</h2>
    <hr>
</div>

<!-- POPULAR PRODUCTS START -->
<div class="popular_products container">
    <?php foreach ($products as $product): ?>
        <?php
            $sql_image = "SELECT path FROM images WHERE product_id = ? ORDER BY id ASC LIMIT 1";
            $stmt_image = $database->prepare($sql_image);
            $stmt_image->execute([$product['id']]);
            $image = $stmt_image->fetch(2);
        ?>

    <div class="product_item">
        <img src="<?=$image['path'] ?>" alt="Васильковое поле">
        <h3><?=$product['title'] ?></h3>
        <p><?=$product['price'] ?> ₽</p>
    </div>
    <?php endforeach; ?>

    <div class="catalog_btn_container">
        <a href="./?page=catalog" class="btn_bg">ПЕРЕЙТИ В КАТАЛОГ</a>
    </div>
</div>
<!-- POPULAR PRODUCTS END -->

<div id="studio" class="h2_title container mt-105">
    <h2>Наша студия</h2>
    <hr>
</div>

<!-- STUDIO SECTION START -->
<div class="studio_section container">
    <div class="studio_image">
        <img src="assets/media/images/index/studio_image-9746d2.png" alt="Наша студия">
    </div>
    <div class="studio_image">
        <img src="assets/media/images/index/studio2.png" alt="Наша студия">
    </div>
    <div class="studio_image">
        <img src="assets/media/images/index/studio3.png" alt="Наша студия">
    </div>
    <div class="studio_image">
        <img src="assets/media/images/index/studio4.png" alt="Наша студия">
    </div>
</div>
<!-- STUDIO SECTION END -->

<div id="faq" class="h2_title container mt-105">
    <h2>FAQ - часто задаваемые вопросы</h2>
    <hr>
</div>

<!-- FAQ SECTION START -->
<div class="faq_section container">
    <div class="faq_item active">
        <div class="faq_question">
            <h4>Я никогда не занимался(ась) гончарным делом. У меня получится?</h4>
        </div>
        <div class="faq_answer">
            <p>Конечно! Большинство наших гостей приходят к нам впервые. Все мастер-классы разработаны для новичков.
                Наш мастер подробно объясняет и показывает каждый этап, индивидуально помогает каждому ученику и
                всегда подсказывает, как исправить небольшие недочёты.</p>
        </div>
    </div>
    <div class="faq_item">
        <div class="faq_question">
            <h4>Как долго сохнет и обжигается готовое изделие? Когда я смогу его забрать?</h4>
        </div>
        <div class="faq_answer">
            <p>Готовое изделие сохнет 3-5 дней, затем обжигается в печи. Полный цикл занимает около 7-10 дней. Мы
                свяжемся с вами, когда изделие будет готово к выдаче.</p>
        </div>
    </div>
    <div class="faq_item">
        <div class="faq_question">
            <h4>Можно ли прийти с детьми?</h4>
        </div>
        <div class="faq_answer">
            <p>Да, мы рады видеть детей от 6 лет в сопровождении взрослых. Для детей у нас есть специальные
                программы и безопасные материалы.</p>
        </div>
    </div>
    <div class="faq_item">
        <div class="faq_question">
            <h4>Что включено в стоимость мастер-класса?</h4>
        </div>
        <div class="faq_answer">
            <p>В стоимость включены все материалы, инструменты, обжиг изделия и работа мастера. Вы заберете с собой
                готовое изделие.</p>
        </div>
    </div>
</div>
<!-- FAQ SECTION END -->



<script>
    // FAQ Accordion functionality
    document.addEventListener('DOMContentLoaded', function() {
        const faqItems = document.querySelectorAll('.faq_item');

        faqItems.forEach(item => {
            const question = item.querySelector('.faq_question');

            question.addEventListener('click', function() {
                // Remove active class from all items
                faqItems.forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.classList.remove('active');
                    }
                });

                // Toggle active class on clicked item
                item.classList.toggle('active');
            });
        });
    });
</script>