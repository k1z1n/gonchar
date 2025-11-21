<?php

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id']) && isset($_SESSION['user_id'])) {
    $product_id = (int)$_POST['product_id'];

    $sql_product = "SELECT count FROM products WHERE id = ?";
    $stmt_product = $database->prepare($sql_product);
    $stmt_product->execute([$product_id]);
    $productData = $stmt_product->fetch(PDO::FETCH_ASSOC);

    if (!$productData || (int)$productData['count'] <= 0) {
        echo "<script>document.location.href='./?page=cart';</script>";
        exit;
    }

    $availableStock = (int)$productData['count'];
    $quantityToAdd = 1;

    $sql_cart = "SELECT * FROM carts WHERE product_id = ? AND user_id = ?";
    $stmt_cart = $database->prepare($sql_cart);
    $stmt_cart->execute([$product_id, $_SESSION['user_id']]);
    $cart = $stmt_cart->fetch(PDO::FETCH_ASSOC);

    if (empty($cart)) {
        $quantityToInsert = min($quantityToAdd, $availableStock);
        $sql_add_cart = "INSERT INTO carts (product_id, user_id, count) VALUES (?, ?, ?)";
        $stmt_add_cart = $database->prepare($sql_add_cart);
        $stmt_add_cart->execute([$product_id, $_SESSION['user_id'], $quantityToInsert]);
    } else {
        $newCount = (int)$cart['count'] + $quantityToAdd;
        if ($newCount > $availableStock) {
            $newCount = $availableStock;
        }
        $sql_add_cart_count = "UPDATE carts SET count = ? WHERE product_id = ? AND user_id = ?";
        $stmt_add_cart_count = $database->prepare($sql_add_cart_count);
        $stmt_add_cart_count->execute([$newCount, $product_id, $_SESSION['user_id']]);
    }

    echo "<script>document.location.href='./?page=cart';</script>";
    exit;
}

// Получаем параметры фильтрации и сортировки
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';
$search = $_GET['search'] ?? '';
$perPage = 9;
$currentPageNumber = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($currentPageNumber < 1) {
    $currentPageNumber = 1;
}

// Загружаем все категории
$sql_categories = "SELECT * FROM category ORDER BY title ASC";
$stmt_categories = $database->prepare($sql_categories);
$stmt_categories->execute();
$categories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);

// Формируем запрос для товаров с фильтрацией по категории и поиском
$baseSql = "FROM products";
$params = [];
$whereConditions = [];

if ($category_id > 0) {
    $whereConditions[] = "category_id = ?";
    $params[] = $category_id;
}

if (!empty($search)) {
    $searchParam = '%' . $search . '%';
    $whereConditions[] = "(title LIKE ? OR (article IS NOT NULL AND article != '' AND article LIKE ?))";
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$whereClause = '';
if (!empty($whereConditions)) {
    $whereClause = " WHERE " . implode(" AND ", $whereConditions);
}
$baseSql .= $whereClause;

$sql_products = "SELECT * " . $baseSql;

// Применяем сортировку
switch ($sort) {
    case 'price-asc':
        $sql_products .= " ORDER BY price ASC";
        break;
    case 'price-desc':
        $sql_products .= " ORDER BY price DESC";
        break;
    case 'newest':
        $sql_products .= " ORDER BY id DESC";
        break;
    case 'popularity':
        // Если есть поле популярности, используем его, иначе по умолчанию
        $sql_products .= " ORDER BY id DESC";
        break;
    default:
        $sql_products .= " ORDER BY id ASC";
        break;
}

$sql_count = "SELECT COUNT(*) " . $baseSql;
$stmt_count = $database->prepare($sql_count);
$stmt_count->execute($params);
$totalProducts = (int)$stmt_count->fetchColumn();

$totalPages = $totalProducts > 0 ? (int)ceil($totalProducts / $perPage) : 1;
if ($currentPageNumber > $totalPages) {
    $currentPageNumber = $totalPages;
}
$offset = ($currentPageNumber - 1) * $perPage;
if ($offset < 0) {
    $offset = 0;
}

$sql_products .= " LIMIT $perPage OFFSET $offset";

$stmt_products = $database->prepare($sql_products);
$stmt_products->execute($params);
$products = $stmt_products->fetchAll(PDO::FETCH_ASSOC);

// Названия для сортировки
$sortNames = [
    'default' => 'По умолчанию',
    'price-asc' => 'По возрастанию цены',
    'price-desc' => 'По убыванию цены',
    'popularity' => 'По популярности',
    'newest' => 'Сначала новые'
];
$currentSortName = $sortNames[$sort] ?? 'По умолчанию';

$paginationBaseParams = [
    'page' => 'catalog'
];
if ($category_id > 0) {
    $paginationBaseParams['category'] = $category_id;
}
if ($sort !== 'default') {
    $paginationBaseParams['sort'] = $sort;
}
if (!empty($search)) {
    $paginationBaseParams['search'] = $search;
}

$buildCatalogUrl = function(array $extraParams = []) use ($paginationBaseParams) {
    $params = array_merge($paginationBaseParams, $extraParams);
    if (isset($params['p']) && ((int)$params['p']) <= 1) {
        unset($params['p']);
    }
    $params = array_filter(
        $params,
        function ($value) {
            return $value !== null && $value !== '';
        }
    );
    return './?' . http_build_query($params);
};

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
                <div class="filter_category <?= $category_id == 0 ? 'active' : '' ?>" data-category="0">
                    ВСЕ ТОВАРЫ
                </div>
                <?php foreach ($categories as $category) : ?>
                    <div class="filter_category <?= $category_id == $category['id'] ? 'active' : '' ?>"
                        data-category="<?= $category['id'] ?>">
                        <?= htmlspecialchars(strtoupper($category['title'])) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- FILTERS SIDEBAR END -->

        <!-- PRODUCTS SECTION START -->
        <div class="products_section">
            <!-- SEARCH FORM START -->
            <div class="catalog_search_container" style="margin-bottom: 20px;">
                <form method="GET" action="" class="catalog_search_form" style="display: flex; gap: 10px; align-items: center;">
                    <input type="hidden" name="page" value="catalog">
                    <?php if ($category_id > 0): ?>
                        <input type="hidden" name="category" value="<?= $category_id ?>">
                    <?php endif; ?>
                    <?php if ($sort != 'default'): ?>
                        <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                    <?php endif; ?>
                    <input type="text"
                        name="search"
                        class="catalog_search_input"
                        placeholder="Поиск по названию или артикулу..."
                        value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                        style="flex: 1; padding: 10px 15px; border: 1px solid #6E1A00; border-radius: 4px; font-size: 14px; outline: none;">
                    <button type="submit" class="catalog_search_btn" style="padding: 10px 20px; background: #6E1A00; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">Найти</button>
                    <?php if (!empty($search)): ?>
                        <a href="<?= htmlspecialchars($buildCatalogUrl(['search' => null, 'p' => 1])) ?>" 
                           class="catalog_search_reset" 
                           style="padding: 10px 20px; background: #ccc; color: #333; text-decoration: none; border-radius: 4px; font-size: 14px;">Сбросить</a>
                    <?php endif; ?>
                </form>
            </div>
            <!-- SEARCH FORM END -->
            
            <div class="sort_dropdown">
                <div class="sort_trigger">
                    <span class="sort_text"><?= htmlspecialchars($currentSortName) ?></span>
                    <img class="sort_arrow" src="assets/media/images/catalog/filtr.svg" alt="">
                </div>
                <div class="sort_options">
                    <div class="sort_option <?= $sort == 'default' ? 'active' : '' ?>" data-value="default">По умолчанию</div>
                    <div class="sort_option <?= $sort == 'price-asc' ? 'active' : '' ?>" data-value="price-asc">По возрастанию цены</div>
                    <div class="sort_option <?= $sort == 'price-desc' ? 'active' : '' ?>" data-value="price-desc">По убыванию цены</div>
                    <div class="sort_option <?= $sort == 'popularity' ? 'active' : '' ?>" data-value="popularity">По популярности</div>
                    <div class="sort_option <?= $sort == 'newest' ? 'active' : '' ?>" data-value="newest">Сначала новые</div>
                </div>
            </div>

            <!-- PRODUCTS GRID START -->
            <div class="products_grid">
                <?php if (empty($products)) : ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                        <h3 style="font-size: 24px; color: #6E1A00; margin-bottom: 20px;">Товары не найдены</h3>
                        <p style="font-size: 16px; color: #6E1A00;">
                            <?php if (!empty($search)): ?>
                                Товары не найдены по запросу "<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>". Попробуйте изменить параметры поиска или выбрать другую категорию.
                            <?php else: ?>
                                Попробуйте выбрать другую категорию или изменить параметры фильтрации
                            <?php endif; ?>
                        </p>
                    </div>
                <?php else : ?>
                    <?php foreach ($products as $product) : ?>
                        <?php
                        $sql_image = "SELECT path FROM images WHERE product_id = ? ORDER BY id ASC LIMIT 1";
                        $stmt_image = $database->prepare($sql_image);
                        $stmt_image->execute([$product['id']]);
                        $image = $stmt_image->fetch(PDO::FETCH_ASSOC);
                        $imagePath = $image ? $image['path'] : 'assets/media/images/index/product_main-69809c.png';
                        ?>
                        <div class="product_card">
                            <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($product['title']) ?>">
                            <h3><?= htmlspecialchars($product['title']) ?></h3>
                            <p class="price"><?= number_format($product['price'], 0, ',', ' ') ?> ₽</p>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <form action="" method="post" class="product_actions">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <a href="./?page=product&&id=<?= $product['id'] ?>" class="btn">ПОДРОБНЕЕ</a>
                                    <button class="btn_bg" type="submit">В КОРЗИНУ</button>
                                </form>
                            <?php else: ?>
                                <div class="product_actions">
                                    <a href="./?page=product&&id=<?= $product['id'] ?>" class="btn">ПОДРОБНЕЕ</a>
                                    <a href="./?page=login" class="btn_bg">В КОРЗИНУ</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <!-- PRODUCTS GRID END -->

        <!-- PAGINATION START -->
        <?php if ($totalPages > 1): ?>
            <?php
            $pagesToDisplay = [];
            if ($totalPages <= 7) {
                $pagesToDisplay = range(1, $totalPages);
            } else {
                $pagesToDisplay[] = 1;
                $range = 2;
                $start = max(2, $currentPageNumber - $range);
                $end = min($totalPages - 1, $currentPageNumber + $range);
                if ($start > 2) {
                    $pagesToDisplay[] = '...';
                }
                for ($i = $start; $i <= $end; $i++) {
                    $pagesToDisplay[] = $i;
                }
                if ($end < $totalPages - 1) {
                    $pagesToDisplay[] = '...';
                }
                $pagesToDisplay[] = $totalPages;
            }
            ?>
            <div class="pagination">
                <?php if ($currentPageNumber > 1): ?>
                    <a class="pagination_btn" href="<?= htmlspecialchars($buildCatalogUrl(['p' => $currentPageNumber - 1])) ?>" aria-label="Предыдущая страница">
                        <img src="assets/media/images/catalog/str.svg" alt="">
                    </a>
                <?php endif; ?>

                <?php foreach ($pagesToDisplay as $pageItem): ?>
                    <?php if ($pageItem === '...'): ?>
                        <span class="pagination_dots">...</span>
                    <?php else: ?>
                        <a class="pagination_number <?= $pageItem === $currentPageNumber ? 'active' : '' ?>"
                           href="<?= htmlspecialchars($buildCatalogUrl(['p' => $pageItem])) ?>">
                            <?= $pageItem ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php if ($currentPageNumber < $totalPages): ?>
                    <a class="pagination_btn" href="<?= htmlspecialchars($buildCatalogUrl(['p' => $currentPageNumber + 1])) ?>" aria-label="Следующая страница">
                        <img src="assets/media/images/catalog/str.svg" alt="">
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <!-- PAGINATION END -->
        </div>
        <!-- PRODUCTS SECTION END -->
    </div>
</div>
<!-- CATALOG CONTENT END -->

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Обработка фильтрации по категориям
        const filterCategories = document.querySelectorAll('.filter_category');
        filterCategories.forEach(category => {
            category.addEventListener('click', function() {
                const categoryId = this.getAttribute('data-category');
                const url = new URL(window.location.href);

                if (categoryId == '0') {
                    url.searchParams.delete('category');
                } else {
                    url.searchParams.set('category', categoryId);
                }
                url.searchParams.delete('p');

                // Сохраняем параметр сортировки при смене категории
                const currentSort = url.searchParams.get('sort');
                if (!currentSort) {
                    url.searchParams.delete('sort');
                }

                // Сохраняем параметр поиска при смене категории
                const currentSearch = url.searchParams.get('search');
                if (!currentSearch) {
                    url.searchParams.delete('search');
                }
                url.searchParams.delete('p');

                window.location.href = url.toString();
            });
        });

        // Обработка выпадающего списка сортировки
        const sortDropdown = document.querySelector('.sort_dropdown');
        const sortTrigger = document.querySelector('.sort_trigger');
        const sortOptions = document.querySelectorAll('.sort_option');
        const sortText = document.querySelector('.sort_text');
        const sortArrow = document.querySelector('.sort_arrow');

        // Открытие/закрытие выпадающего списка
        sortTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            sortDropdown.classList.toggle('active');
            sortArrow.style.transform = sortDropdown.classList.contains('active') ? 'rotate(180deg)' : 'rotate(0deg)';
        });

        // Закрытие при клике вне списка
        document.addEventListener('click', function(e) {
            if (!sortDropdown.contains(e.target)) {
                sortDropdown.classList.remove('active');
                sortArrow.style.transform = 'rotate(0deg)';
            }
        });

        // Выбор опции сортировки
        sortOptions.forEach(option => {
            option.addEventListener('click', function(e) {
                e.stopPropagation();
                const sortValue = this.getAttribute('data-value');
                const url = new URL(window.location.href);

                if (sortValue === 'default') {
                    url.searchParams.delete('sort');
                } else {
                    url.searchParams.set('sort', sortValue);
                }
                url.searchParams.delete('p');

                // Сохраняем параметр категории при смене сортировки
                const currentCategory = url.searchParams.get('category');
                if (!currentCategory) {
                    url.searchParams.delete('category');
                }

                // Сохраняем параметр поиска при смене сортировки
                const currentSearch = url.searchParams.get('search');
                if (!currentSearch) {
                    url.searchParams.delete('search');
                }
                url.searchParams.delete('p');

                window.location.href = url.toString();
            });
        });
    });
</script>