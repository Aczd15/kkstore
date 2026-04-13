<h1>Личный кабинет</h1>
<p>Здравствуйте, <strong><?= e($user['name']) ?></strong>. Ваша роль: <strong><?= e($user['role']) ?></strong>.</p>

<div class="grid">
    <article class="card">
        <h3>Быстрые действия</h3>
        <ul>
            <li><a href="<?= BASE_URL ?>/index.php?page=catalog">Перейти в каталог</a></li>
            <li><a href="<?= BASE_URL ?>/index.php?page=services">Посмотреть услуги сервиса</a></li>
            <?php if ($user['role'] === 'manager'): ?>
                <li><a href="<?= BASE_URL ?>/index.php?page=manager">Подать заявку на товар</a></li>
            <?php endif; ?>
            <?php if ($user['role'] === 'admin'): ?>
                <li><a href="<?= BASE_URL ?>/index.php?page=admin">Открыть админ-панель</a></li>
            <?php endif; ?>
        </ul>
    </article>

    <article class="card">
        <h3>Информация</h3>
        <p>В личном кабинете вы можете просматривать новости компании, управлять заявками (для менеджеров) и модерацией каталога (для администраторов).</p>
    </article>
</div>
