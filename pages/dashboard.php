<?php
$repairStmt = db()->prepare('SELECT * FROM repair_requests WHERE user_id = :id ORDER BY created_at DESC');
$repairStmt->execute(['id' => $user['id']]);
$repairs = $repairStmt->fetchAll();
$reviewStmt = db()->prepare('SELECT pr.*, p.name AS product_name FROM product_reviews pr JOIN products p ON p.id = pr.product_id WHERE pr.user_id = :id ORDER BY pr.created_at DESC');
$reviewStmt->execute(['id' => $user['id']]);
$myReviews = $reviewStmt->fetchAll();
?>

<h1>Личный кабинет</h1>
<p>Здравствуйте, <strong><?= e($user['name']) ?></strong>. Ваша роль: <strong><?= e($user['role']) ?></strong>. В этом разделе вы можете управлять профилем, безопасностью и своими заявками.</p>

<div class="grid">
    <article class="card">
        <h3>Профиль</h3>
        <p>
            <?php if (!empty($user['avatar_path'])): ?>
                <img class="avatar-large" src="<?= BASE_URL . e($user['avatar_path']) ?>" alt="avatar">
            <?php else: ?>
                <span class="badge pending">Аватар не загружен</span>
            <?php endif; ?>
        </p>
        <form method="post" enctype="multipart/form-data" action="<?= BASE_URL ?>/index.php?page=dashboard">
            <input type="hidden" name="action" value="update_avatar">
            <label>Сменить аватар</label>
            <input type="file" name="avatar" accept="image/png,image/jpeg,image/webp" required>
            <button class="btn" type="submit">Обновить аватар</button>
        </form>
    </article>

    <article class="card">
        <h3>Смена пароля</h3>
        <form method="post" action="<?= BASE_URL ?>/index.php?page=dashboard">
            <input type="hidden" name="action" value="change_password">
            <label>Текущий пароль</label>
            <input type="password" name="current_password" required>
            <label>Новый пароль (минимум 8 символов)</label>
            <input type="password" name="new_password" required>
            <label>Повторите новый пароль</label>
            <input type="password" name="repeat_password" required>
            <button class="btn" type="submit">Сменить пароль</button>
        </form>
    </article>
</div>

<div class="grid">
    <article class="card">
        <h3>Быстрые действия</h3>
        <ul>
            <li><a href="<?= BASE_URL ?>/index.php?page=catalog">Перейти в каталог</a></li>
            <li><a href="<?= BASE_URL ?>/index.php?page=services">Посмотреть услуги сервиса</a></li>
            <li><a href="<?= BASE_URL ?>/index.php?page=repairs">Подать заявку на ремонт</a></li>
            <?php if ($user['role'] === 'manager'): ?>
                <li><a href="<?= BASE_URL ?>/index.php?page=manager">Подать заявку на товар</a></li>
            <?php endif; ?>
            <?php if ($user['role'] === 'admin'): ?>
                <li><a href="<?= BASE_URL ?>/index.php?page=admin">Открыть админ-панель</a></li>
            <?php endif; ?>
        </ul>
    </article>

    <article class="card">
        <h3>Мои заявки на ремонт</h3>
        <table class="table">
            <thead><tr><th>Устройство</th><th>Проблема</th><th>Статус</th><th>Причина решения</th></tr></thead>
            <tbody>
            <?php foreach ($repairs as $item): ?>
                <tr>
                    <td><?= e($item['brand']) ?> <?= e($item['model']) ?></td>
                    <td><?= e($item['issue_description']) ?></td>
                    <td><span class="badge <?= e($item['status']) ?>"><?= e($item['status']) ?></span></td>
                    <td><?= e($item['decision_reason'] ?? '—') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </article>
</div>


<div class="card">
    <h3>Мои отзывы о товарах</h3>
    <table class="table">
        <thead><tr><th>Товар</th><th>Оценка</th><th>Комментарий</th><th>Статус</th></tr></thead>
        <tbody>
        <?php foreach ($myReviews as $item): ?>
            <tr>
                <td><?= e($item['product_name']) ?></td>
                <td><?= (int)$item['rating'] ?>/5</td>
                <td><?= e($item['comment']) ?></td>
                <td><span class="badge <?= e($item['status']) ?>"><?= e($item['status']) ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
