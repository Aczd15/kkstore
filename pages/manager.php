<?php
$categories = db()->query('SELECT * FROM categories ORDER BY title')->fetchAll();
$requestsStmt = db()->prepare('SELECT r.*, c.title AS category_name FROM product_requests r JOIN categories c ON c.id = r.category_id WHERE manager_id = :id ORDER BY r.created_at DESC');
$requestsStmt->execute(['id' => $user['id']]);
$requests = $requestsStmt->fetchAll();
?>

<h1>Панель менеджера</h1>
<p>Менеджер может предложить новый товар в каталог. После отправки заявка попадает администратору на модерацию.</p>

<form method="post" action="<?= BASE_URL ?>/index.php?page=manager">
    <input type="hidden" name="action" value="manager_request">
    <label>Название товара</label>
    <input type="text" name="name" required>
    <label>Бренд</label>
    <input type="text" name="brand" required>
    <label>Категория</label>
    <select name="category_id" required>
        <?php foreach ($categories as $category): ?>
            <option value="<?= $category['id'] ?>"><?= e($category['title']) ?></option>
        <?php endforeach; ?>
    </select>
    <label>Цена</label>
    <input type="number" name="price" step="0.01" required>
    <label>Описание</label>
    <textarea name="description" rows="4" required></textarea>
    <button class="btn" type="submit">Отправить заявку</button>
</form>

<h2>Мои заявки</h2>
<table class="table">
    <thead><tr><th>Товар</th><th>Категория</th><th>Цена</th><th>Статус</th><th>Дата</th></tr></thead>
    <tbody>
    <?php foreach ($requests as $item): ?>
        <tr>
            <td><?= e($item['name']) ?> (<?= e($item['brand']) ?>)</td>
            <td><?= e($item['category_name']) ?></td>
            <td><?= number_format((float)$item['price'], 0, '.', ' ') ?> ₽</td>
            <td><span class="badge <?= e($item['status']) ?>"><?= e($item['status']) ?></span></td>
            <td><?= e($item['created_at']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
