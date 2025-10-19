<?php

?>

<!-- HERO BANNER START -->
<section class="catalog_hero">
    <h1>Создано с любовью. Для вашего уюта.</h1>
</section>
<!-- HERO BANNER END -->

<!-- CATALOG CONTENT START -->
<div class="catalog_content container">
    <div class="catalog_layout">
        <!-- FILTERS SIDEBAR START -->
        <div class="filters_sidebar">
            <div class="filter_categories">
                <div class="filter_category active">ВСЕ ТОВАРЫ</div>
                <div class="filter_category">ВАЗА</div>
                <div class="filter_category">КРУЖКА</div>
                <div class="filter_category">ТАРЕЛКИ</div>
                <div class="filter_category">ДРУГОЕ</div>
            </div>
        </div>
        <!-- FILTERS SIDEBAR END -->

        <!-- PRODUCTS SECTION START -->
        <div class="products_section">
            <div class="sort_dropdown">
                <div class="sort_trigger">
                    <span class="sort_text">По умолчанию</span>
                    <img class="sort_arrow" src="assets/media/images/catalog/filtr.svg" alt="">
                </div>
                <div class="sort_options">
                    <div class="sort_option" data-value="default">По умолчанию</div>
                    <div class="sort_option" data-value="price-asc">По возрастанию цены</div>
                    <div class="sort_option" data-value="price-desc">По убыванию цены</div>
                    <div class="sort_option" data-value="popularity">По популярности</div>
                    <div class="sort_option" data-value="newest">Сначала новые</div>
                </div>
            </div>

            <!-- PRODUCTS GRID START -->
            <div class="products_grid">
                <!-- Product 1 -->
                <div class="product_card">
                    <img src="assets/media/images/index/product1-1e853d.png" alt="Васильковое поле">
                    <h3>ВАСИЛЬКОВОЕ ПОЛЕ</h3>
                    <p class="price">2 700 ₽</p>
                    <div class="product_actions">
                        <a href="./?page=product&&id" class="btn">ПОДРОБНЕЕ</a>
                        <button class="btn_bg">В КОРЗИНУ</button>
                    </div>
                </div>


            </div>
            <!-- PRODUCTS GRID END -->

            <!-- PAGINATION START -->
            <div class="pagination">
                <button class="pagination_btn">
                    <img src="assets/media/images/catalog/str.svg" alt="">
                </button>
                <span class="pagination_number active">1</span>
                <span class="pagination_number">2</span>
                <span class="pagination_dots">...</span>
                <span class="pagination_number">5</span>
                <button class="pagination_btn">
                    <img src="assets/media/images/catalog/str.svg" alt="">
                </button>
            </div>
            <!-- PAGINATION END -->
        </div>
        <!-- PRODUCTS SECTION END -->
    </div>
</div>
<!-- CATALOG CONTENT END -->
