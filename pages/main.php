<?php

$sql_products = "SELECT * FROM products ORDER BY RAND() LIMIT 4";
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
        <button class="btn_bg" id="bannerMasterclassBtn" type="button">ЗАПИСАТЬСЯ</button>
    </div>
</section>
<!-- BANNER END -->

<!-- MASTERCLASS MODAL START -->
<div class="modal_backdrop" id="masterclassModal" aria-hidden="true">
    <div class="modal_overlay" data-close="modal"></div>
    <div class="modal_dialog" role="dialog" aria-modal="true" aria-labelledby="masterclassModalTitle">
        <button class="modal_close" type="button" aria-label="Закрыть" data-close="modal">&times;</button>
        <h3 id="masterclassModalTitle">Запись на мастер-класс</h3>
        <p>Оставьте контакты, и мы свяжемся, чтобы уточнить детали записи.</p>
        <form id="masterclassForm">
            <input type="hidden" name="action" value="masterclass_request">
            <div class="form_group">
                <label for="modalFullName">ФИО *</label>
                <input type="text" id="modalFullName" name="full_name" placeholder="Иванов Иван Иванович" required>
            </div>
            <div class="form_group">
                <label for="modalPhone">Телефон *</label>
                <input type="tel" id="modalPhone" name="phone" placeholder="+7 (999) 000-00-00" inputmode="tel" required>
            </div>
            <div class="form_group">
                <label for="modalCallTime">Когда вам удобнее принять звонок? *</label>
                <select id="modalCallTime" name="call_time" required>
                    <option value="" disabled selected>Выберите интервал</option>
                    <option value="10:00-12:00">10:00–12:00</option>
                    <option value="12:00-15:00">12:00–15:00</option>
                    <option value="15:00-18:00">15:00–18:00</option>
                    <option value="18:00-21:00">18:00–21:00</option>
                    <option value="В любое время">В любое время</option>
                </select>
            </div>
            <button class="btn_bg" type="submit" id="masterclassSubmitBtn">ОТПРАВИТЬ ЗАЯВКУ</button>
            <div class="modal_feedback" id="masterclassFeedback" aria-live="polite"></div>
        </form>
    </div>
</div>
<!-- MASTERCLASS MODAL END -->

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
        <p><?= number_format($product['price'], 0, ',', ' ') ?> ₽</p>
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
    document.addEventListener('DOMContentLoaded', function() {
        const faqItems = document.querySelectorAll('.faq_item');

        faqItems.forEach(item => {
            const question = item.querySelector('.faq_question');

            question.addEventListener('click', function() {
                faqItems.forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.classList.remove('active');
                    }
                });

                item.classList.toggle('active');
            });
        });

        const body = document.body;
        const masterclassModal = document.getElementById('masterclassModal');
        const openMasterclassBtn = document.getElementById('bannerMasterclassBtn');
        const masterclassForm = document.getElementById('masterclassForm');
        const masterclassFeedback = document.getElementById('masterclassFeedback');
        const masterclassSubmitBtn = document.getElementById('masterclassSubmitBtn');

        const closeTargets = masterclassModal ? masterclassModal.querySelectorAll('[data-close="modal"]') : [];

        const lockScroll = () => body.classList.add('modal-open');
        const unlockScroll = () => body.classList.remove('modal-open');

        const openModal = () => {
            if (!masterclassModal) return;
            masterclassModal.classList.add('is-visible');
            lockScroll();
            if (masterclassFeedback) {
                masterclassFeedback.textContent = '';
                masterclassFeedback.classList.remove('is-error', 'is-success');
            }
        };

        const closeModal = () => {
            if (!masterclassModal) return;
            masterclassModal.classList.remove('is-visible');
            unlockScroll();
        };

        openMasterclassBtn?.addEventListener('click', openModal);
        closeTargets.forEach(el => el.addEventListener('click', closeModal));

        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && masterclassModal?.classList.contains('is-visible')) {
                closeModal();
            }
        });

        masterclassForm?.addEventListener('submit', async event => {
            event.preventDefault();

            if (!masterclassForm.checkValidity()) {
                masterclassForm.reportValidity();
                return;
            }

            const formData = new FormData(masterclassForm);

            if (masterclassSubmitBtn) {
                masterclassSubmitBtn.disabled = true;
                masterclassSubmitBtn.textContent = 'ОТПРАВЛЯЕМ...';
            }

            if (masterclassFeedback) {
                masterclassFeedback.textContent = '';
                masterclassFeedback.classList.remove('is-error', 'is-success');
            }

            try {
                const response = await fetch('?page=masterclass_request', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    masterclassFeedback.textContent = data.message || 'Заявка успешно отправлена!';
                    masterclassFeedback.classList.add('is-success');
                    masterclassFeedback.classList.remove('is-error');
                    masterclassForm.reset();
                    setTimeout(closeModal, 1800);
                } else {
                    const message = data.errors ? Object.values(data.errors).join('. ') : (data.message || 'Не удалось отправить заявку.');
                    masterclassFeedback.textContent = message;
                    masterclassFeedback.classList.add('is-error');
                    masterclassFeedback.classList.remove('is-success');
                }
            } catch (error) {
                if (masterclassFeedback) {
                    masterclassFeedback.textContent = 'Произошла ошибка. Попробуйте ещё раз позже.';
                    masterclassFeedback.classList.add('is-error');
                    masterclassFeedback.classList.remove('is-success');
                }
            } finally {
                if (masterclassSubmitBtn) {
                    masterclassSubmitBtn.disabled = false;
                    masterclassSubmitBtn.textContent = 'ОТПРАВИТЬ ЗАЯВКУ';
                }
            }
        });
    });
</script>