<?php
$categories = db()->query('SELECT * FROM categories ORDER BY id DESC')->fetchAll();
$products = db()->query('SELECT p.*, c.title AS category_name FROM products p JOIN categories c ON c.id = p.category_id ORDER BY p.id DESC LIMIT 50')->fetchAll();
$news = db()->query('SELECT * FROM news ORDER BY id DESC LIMIT 20')->fetchAll();
$requests = db()->query('SELECT r.*, u.name AS manager_name, c.title AS category_name FROM product_requests r JOIN users u ON u.id = r.manager_id JOIN categories c ON c.id = r.category_id ORDER BY r.id DESC')->fetchAll();
$repairRequests = db()->query('SELECT rr.*, u.name AS user_name FROM repair_requests rr JOIN users u ON u.id = rr.user_id ORDER BY rr.id DESC')->fetchAll();
?>

<h1>Админ-панель</h1>
<p>Администратор управляет каталогом, категориями, новостями, модерацией заявок менеджеров и заявок на ремонт. Для каждой заявки на ремонт решение принимается только с обязательной причиной.</p>

<section class="grid">
    <article class="card">
        <h3>Добавить категорию</h3>
        <form method="post" action="<?= BASE_URL ?>/index.php?page=admin">
            <input type="hidden" name="action" value="add_category">
            <label>Название категории</label>
            <input type="text" name="title" required>
            <button class="btn" type="submit">Добавить</button>
        </form>
    </article>

    <article class="card">
        <h3>Добавить новость</h3>
        <form method="post" action="<?= BASE_URL ?>/index.php?page=admin">
            <input type="hidden" name="action" value="add_news">
            <label>Заголовок</label>
            <input type="text" name="title" required>
            <label>Текст новости</label>
            <textarea name="content" rows="6" required></textarea>
            <button class="btn" type="submit">Опубликовать</button>
        </form>
    </article>
</section>

<section class="card">
    <h3>Добавить товар</h3>
    <form method="post" action="<?= BASE_URL ?>/index.php?page=admin" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_product">
        <div class="grid">
            <div>
                <label>Название</label>
                <input type="text" name="name" required>
            </div>
            <div>
                <label>Бренд</label>
                <input type="text" name="brand" required>
            </div>
            <div>
                <label>Категория</label>
                <select name="category_id" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>"><?= e($category['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Цена</label>
                <input type="number" name="price" step="0.01" required>
            </div>
            <div>
                <label>Остаток</label>
                <input type="number" name="stock" required>
            </div>
            <div>
                <label>Фото товара</label>
                <input type="file" name="product_image" accept="image/png,image/jpeg,image/webp">
            </div>
        </div>
        <label>Описание</label>
        <textarea name="description" rows="4" required></textarea>
        <label><input type="checkbox" name="is_featured"> Рекомендуемый товар</label>
        <button class="btn" type="submit">Сохранить товар</button>
    </form>
</section>

<section class="card">
    <h3>Заявки менеджеров</h3>
    <table class="table">
        <thead><tr><th>Менеджер</th><th>Товар</th><th>Категория</th><th>Цена</th><th>Статус</th><th>Действия</th></tr></thead>
        <tbody>
        <?php foreach ($requests as $item): ?>
            <tr>
                <td><?= e($item['manager_name']) ?></td>
                <td><?= e($item['name']) ?> (<?= e($item['brand']) ?>)</td>
                <td><?= e($item['category_name']) ?></td>
                <td><?= number_format((float)$item['price'], 0, '.', ' ') ?> ₽</td>
                <td><span class="badge <?= e($item['status']) ?>"><?= e($item['status']) ?></span></td>
                <td>
                    <?php if ($item['status'] === 'pending'): ?>
                        <form class="inline-form" method="post" action="<?= BASE_URL ?>/index.php?page=admin">
                            <input type="hidden" name="action" value="request_decision">
                            <input type="hidden" name="request_id" value="<?= $item['id'] ?>">
                            <input type="hidden" name="decision" value="approve">
                            <button class="btn" type="submit">Принять</button>
                        </form>
                        <form class="inline-form" method="post" action="<?= BASE_URL ?>/index.php?page=admin">
                            <input type="hidden" name="action" value="request_decision">
                            <input type="hidden" name="request_id" value="<?= $item['id'] ?>">
                            <input type="hidden" name="decision" value="reject">
                            <button class="btn danger" type="submit">Отклонить</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<section class="card">
    <h3>Заявки на ремонт от пользователей</h3>
    <table class="table">
        <thead><tr><th>Клиент</th><th>Устройство</th><th>Проблема</th><th>Статус</th><th>Причина</th><th>Решение</th></tr></thead>
        <tbody>
        <?php foreach ($repairRequests as $item): ?>
            <tr>
                <td><?= e($item['user_name']) ?></td>
                <td><?= e($item['device_type']) ?>: <?= e($item['brand']) ?> <?= e($item['model']) ?></td>
                <td><?= e($item['issue_description']) ?></td>
                <td><span class="badge <?= e($item['status']) ?>"><?= e($item['status']) ?></span></td>
                <td><?= e($item['decision_reason'] ?? '—') ?></td>
                <td>
                    <?php if ($item['status'] === 'pending'): ?>
                        <form method="post" action="<?= BASE_URL ?>/index.php?page=admin">
                            <input type="hidden" name="action" value="repair_decision">
                            <input type="hidden" name="repair_id" value="<?= $item['id'] ?>">
                            <label>Решение</label>
                            <select name="decision" required>
                                <option value="approve">Принять</option>
                                <option value="reject">Отклонить</option>
                            </select>
                            <label>Причина (обязательно)</label>
                            <textarea name="reason" rows="2" required></textarea>
                            <button class="btn" type="submit">Сохранить</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<section class="grid">
    <article class="card">
        <h3>Категории</h3>
        <ul>
            <?php foreach ($categories as $item): ?>
                <li><?= e($item['title']) ?></li>
            <?php endforeach; ?>
        </ul>
    </article>
    <article class="card">
        <h3>Последние новости</h3>
        <ul>
            <?php foreach ($news as $item): ?>
                <li><?= e($item['title']) ?></li>
            <?php endforeach; ?>
        </ul>
    </article>
    <article class="card">
        <h3>Товары (последние 50)</h3>
        <ul>
            <?php foreach ($products as $item): ?>
                <li><?= e($item['name']) ?> — <?= number_format((float)$item['price'], 0, '.', ' ') ?> ₽</li>
            <?php endforeach; ?>
        </ul>
    </article>
</section>
