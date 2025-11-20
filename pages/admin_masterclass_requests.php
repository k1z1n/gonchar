<?php

if(isset($_SESSION['user_id'])) {
    if($USER['role'] !== 'admin') {
        header('Location: ./?page=login');
    }
}

$search = trim($_GET['search'] ?? '');
$callTimeFilter = $_GET['call_time'] ?? '';

$allowedCallTimes = [
    '10:00-12:00',
    '12:00-15:00',
    '15:00-18:00',
    '18:00-21:00',
    'В любое время',
];

$statusOptions = [
    'new' => 'Новая',
    'booked' => 'Записали',
];

$sql = "SELECT * FROM masterclass_requests";
$conditions = [];
$params = [];

if ($search !== '') {
    $conditions[] = "(full_name LIKE ? OR phone LIKE ?)";
    $searchParam = '%' . $search . '%';
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if ($callTimeFilter !== '' && in_array($callTimeFilter, $allowedCallTimes, true)) {
    $conditions[] = "call_time = ?";
    $params[] = $callTimeFilter;
}

if (!empty($conditions)) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}

$sql .= ' ORDER BY created_at DESC';

$stmt = $database->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

$requestsCount = count($requests);

?>

<div class="admin-container container mt-105">

    <?php include('./includes/admin_menu.php'); ?>

    <main class="admin-main-content">
        <div class="admin-header">
            <h2 class="admin-page-title">Записи на мастер-класс</h2>
            <div class="admin-header-actions">
                <span class="admin-stat-badge">Всего заявок: <?=$requestsCount?></span>
                <a href="?exit">Выйти</a>
            </div>
        </div>

        <div class="admin-search-container">
            <form method="GET" action="" class="admin-search-form admin-search-form--filters">
                <input type="hidden" name="page" value="admin_masterclass_requests">

                <input
                    type="text"
                    name="search"
                    class="admin-search-input"
                    placeholder="Поиск по ФИО или телефону..."
                    value="<?=htmlspecialchars($search, ENT_QUOTES, 'UTF-8')?>"
                >

                <select name="call_time" class="admin-search-input">
                    <option value="">Все интервалы</option>
                    <?php foreach ($allowedCallTimes as $timeOption): ?>
                        <option value="<?=htmlspecialchars($timeOption, ENT_QUOTES, 'UTF-8')?>"
                            <?=$callTimeFilter === $timeOption ? 'selected' : ''?>>
                            <?=$timeOption?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="admin-btn admin-btn-primary">Применить</button>

                <?php if ($search !== '' || ($callTimeFilter !== '' && in_array($callTimeFilter, $allowedCallTimes, true))): ?>
                    <a href="?page=admin_masterclass_requests" class="admin-btn admin-btn-secondary">Сбросить</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>ФИО</th>
                    <th>Телефон</th>
                    <th>Время для звонка</th>
                    <th>Статус</th>
                    <th>Действия</th>
                    <th>Оставлена</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($requestsCount > 0): ?>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?= (int)$request['id'] ?></td>
                            <td><?= htmlspecialchars($request['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $request['phone'] ?></td>
                            <td><?= htmlspecialchars($request['call_time'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php
                                $statusKey = $request['status'] ?? 'new';
                                $statusLabel = $statusOptions[$statusKey] ?? ucfirst($statusKey);
                                ?>
                                <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td>
                                <a class="btn_bg" href="./?page=admin_masterclass_request_edit&id=<?=(int)$request['id']?>">Изменить статус</a>
                            </td>
                            <td>
                                <?php
                                $createdAt = $request['created_at'] ?? '';
                                $createdAtFormatted = $createdAt;
                                if (!empty($createdAt)) {
                                    $date = DateTime::createFromFormat('Y-m-d H:i:s', $createdAt);
                                    if ($date) {
                                        $createdAtFormatted = $date->format('d.m.Y H:i');
                                    }
                                }
                                ?>
                                <?= htmlspecialchars($createdAtFormatted, ENT_QUOTES, 'UTF-8') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px;">
                            <?php if ($search !== '' || ($callTimeFilter !== '' && in_array($callTimeFilter, $allowedCallTimes, true))): ?>
                                Заявки не найдены по выбранным параметрам.
                            <?php else: ?>
                                Пока нет заявок на мастер-класс.
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="admin-user-cards">
            <?php if ($requestsCount > 0): ?>
                <?php foreach ($requests as $request): ?>
                <div class="admin-user-card">
                    <div class="admin-user-card-header">
                        <span class="admin-user-id">Заявка №<?= (int)$request['id'] ?></span>
                        <?php
                        $statusKey = $request['status'] ?? 'new';
                        $statusLabel = $statusOptions[$statusKey] ?? ucfirst($statusKey);
                        ?>
                        <span class="admin-user-status">
                            <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>
                    <div class="admin-user-info">
                        <div class="admin-user-info-item">
                            <span class="admin-user-info-label">ФИО:</span>
                            <?= htmlspecialchars($request['full_name'], ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <div class="admin-user-info-item">
                            <span class="admin-user-info-label">Телефон:</span>
                            <?= $request['phone'] ?>
                        </div>
                        <div class="admin-user-info-item">
                            <span class="admin-user-info-label">Желаемое время:</span>
                            <?= htmlspecialchars($request['call_time'], ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <div class="admin-user-info-item">
                            <span class="admin-user-info-label">Создана:</span>
                            <?php
                            $createdAt = $request['created_at'] ?? '';
                            $createdAtFormatted = $createdAt;
                            if (!empty($createdAt)) {
                                $date = DateTime::createFromFormat('Y-m-d H:i:s', $createdAt);
                                if ($date) {
                                    $createdAtFormatted = $date->format('d.m.Y H:i');
                                }
                            }
                            ?>
                            <?= htmlspecialchars($createdAtFormatted, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    </div>
                    <div class="admin-user-actions">
                        <a class="admin-btn admin-btn-primary" href="tel:<?= $request['phone'] ?>">Позвонить</a>
                        <a class="btn_bg" href="./?page=admin_masterclass_request_edit&id=<?=(int)$request['id']?>">Изменить статус</a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="admin-user-card" style="text-align: center; padding: 40px;">
                    <?php if ($search !== '' || ($callTimeFilter !== '' && in_array($callTimeFilter, $allowedCallTimes, true))): ?>
                        Заявки не найдены по выбранным параметрам.
                    <?php else: ?>
                        Пока нет заявок на мастер-класс.
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

