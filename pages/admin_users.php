<?php

if(isset($_SESSION['user_id'])) {
    if($USER['role'] !== 'admin') {
        header('Location: ./?page=login');
    }
}

$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM users";
$params = [];

if (!empty($search)) {
    $sql .= " WHERE (surname LIKE ? OR username LIKE ? OR CONCAT(surname, ' ', username) LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $searchParam = '%' . $search . '%';
    $params = [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam];
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $userId = $_POST['userId'];
    // Предотвращаем блокировку самого себя
    if(isset($USER['id']) && $userId == $USER['id']){
        header('Location: ./?page=admin_users' . (!empty($search) ? '&search=' . urlencode($search) : ''));
        exit;
    }
    $stmt = $database->prepare("UPDATE users SET status = 1 - status WHERE id = ?");
    $stmt->execute([$userId]);
    header('Location: ./?page=admin_users' . (!empty($search) ? '&search=' . urlencode($search) : ''));
    exit;
}

$stmt = $database->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(2);

?>

<div class="admin-container container mt-105">

    <?php include('./includes/admin_menu.php'); ?>

    <!-- Основной контент -->
    <main class="admin-main-content">
        <div class="admin-header">
            <h2 class="admin-page-title">Управление пользователями</h2>
            <a href="?exit">Выйти</a>
        </div>

        <div class="admin-search-container">
            <form method="GET" action="" class="admin-search-form">
                <input type="hidden" name="page" value="admin_users">
                <input type="text" 
                       name="search" 
                       class="admin-search-input" 
                       placeholder="Поиск по имени, email или телефону..." 
                       value="<?=htmlspecialchars($search, ENT_QUOTES, 'UTF-8')?>">
                <button type="submit" class="admin-btn admin-btn-primary">Найти</button>
                <?php if (!empty($search)): ?>
                    <a href="?page=admin_users" class="admin-btn admin-btn-secondary">Сбросить</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Имя</th>
                    <th>Email</th>
                    <th>Телефон</th>
                    <th>Блокировка</th>
                    <th>Действие</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?=$user['id'] ?></td>
                        <td><?=$user['surname'] . " " . $user['username'] ?></td>
                        <td><?=$user['email'] ?></td>
                        <td><?=$user['phone'] ?></td>
                        <td><?=$user['status'] ? 'Заблокирован' : 'Активен'?></td>
                        <td>
                            <?php if(isset($USER['id']) && $user['id'] == $USER['id']): ?>
                                <span class="admin-btn admin-btn-disabled" style="opacity: 0.5; cursor: not-allowed;">Недоступно</span>
                            <?php else: ?>
                                <form action="" method="post">
                                    <input type="hidden" name="userId" value="<?= $user['id']?>">
                                    <button type="submit" class="admin-btn <?= $user['status'] ? 'admin-btn-success' : 'admin-btn-danger' ?>"><?= $user['status'] ? 'Разблокировать' : 'Заблокировать' ?></button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px;">
                            <?php if (!empty($search)): ?>
                                Пользователи не найдены по запросу "<?=htmlspecialchars($search, ENT_QUOTES, 'UTF-8')?>"
                            <?php else: ?>
                                Пользователи не найдены
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile user cards -->
        <div class="admin-user-cards">
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                <div class="admin-user-card">
                    <div class="admin-user-card-header">
                        <span class="admin-user-id">ID: <?=$user['id'] ?></span>
                        <span class="admin-user-status admin-status-badge admin-status-new">Активен</span>
                    </div>
                    <div class="admin-user-info">
                        <div class="admin-user-info-item">
                            <span class="admin-user-info-label">Имя:</span>
                            <?=$user['surname'] . " " . $user['username'] ?>
                        </div>
                        <div class="admin-user-info-item">
                            <span class="admin-user-info-label">Email:</span>
                            <?=$user['email'] ?>
                        </div>
                        <div class="admin-user-info-item">
                            <span class="admin-user-info-label">Телефон:</span>
                            <?=$user['phone'] ?>
                        </div>
                    </div>
                    <div class="admin-user-actions">
                        <?php if(isset($USER['id']) && $user['id'] == $USER['id']): ?>
                            <span class="admin-btn admin-btn-disabled" style="opacity: 0.5; cursor: not-allowed;">Недоступно</span>
                        <?php else: ?>
                            <form action="" method="post">
                                <input type="hidden" name="userId" value="<?= $user['id']?>">
                                <button type="submit" class="admin-btn <?= $user['status'] ? 'admin-btn-success' : 'admin-btn-danger' ?>"><?= $user['status'] ? 'Разблокировать' : 'Заблокировать' ?></button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="admin-user-card" style="text-align: center; padding: 40px;">
                    <?php if (!empty($search)): ?>
                        Пользователи не найдены по запросу "<?=htmlspecialchars($search, ENT_QUOTES, 'UTF-8')?>"
                    <?php else: ?>
                        Пользователи не найдены
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
