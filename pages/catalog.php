<?php
$category = (int)($_GET['category'] ?? 0);
$brand = trim($_GET['brand'] ?? '');
$maxPrice = (float)($_GET['max_price'] ?? 0);

$categories = db()->query('SELECT * FROM categories ORDER BY title')->fetchAll();

$sql = 'SELECT p.*, c.title AS category_name, ROUND(AVG(pr.rating), 1) AS avg_rating, COUNT(pr.id) AS rating_count
        FROM products p
        JOIN categories c ON c.id = p.category_id
        LEFT JOIN product_reviews pr ON pr.product_id = p.id AND pr.status = "approved"
        WHERE 1=1';
$params = [];
if ($category > 0) {
    $sql .= ' AND p.category_id = :category';
    $params['category'] = $category;
}
if ($brand !== '') {
    $sql .= ' AND p.brand = :brand';
    $params['brand'] = $brand;
}
if ($maxPrice > 0) {
    $sql .= ' AND p.price <= :max_price';
    $params['max_price'] = $maxPrice;
}
$sql .= ' GROUP BY p.id ORDER BY avg_rating DESC, p.created_at DESC';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$brands = db()->query('SELECT DISTINCT brand FROM products ORDER BY brand')->fetchAll();
$reviews = db()->query('SELECT pr.*, p.name AS product_name, u.name AS user_name FROM product_reviews pr JOIN products p ON p.id = pr.product_id JOIN users u ON u.id = pr.user_id WHERE pr.status = "approved" ORDER BY pr.created_at DESC LIMIT 8')->fetchAll();
?>

<h1>Каталог товаров</h1>
<p>В каталоге KKStore собраны популярные смартфоны, планшеты, ноутбуки и аксессуары от ведущих брендов. Используйте фильтры для быстрого подбора по категории, бренду и цене.</p>

<div class="card long-text">
    <p>Мы добавили систему отзывов и рейтингов: теперь пользователи могут делиться впечатлениями о товарах, а вы можете ориентироваться на реальные оценки перед покупкой.</p>
    <p>Все отзывы проходят модерацию администратора, чтобы в каталоге оставалась только полезная и конструктивная информация.</p>
</div>

<form method="get" class="filters">
    <input type="hidden" name="page" value="catalog">
    <div>
        <label>Категория</label>
        <select name="category">
            <option value="0">Все категории</option>
            <?php foreach ($categories as $item): ?>
                <option value="<?= $item['id'] ?>" <?= $category === (int)$item['id'] ? 'selected' : '' ?>><?= e($item['title']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label>Бренд</label>
        <select name="brand">
            <option value="">Все бренды</option>
            <?php foreach ($brands as $item): ?>
                <option value="<?= e($item['brand']) ?>" <?= $brand === $item['brand'] ? 'selected' : '' ?>><?= e($item['brand']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label>Цена до (₽)</label>
        <input type="number" name="max_price" value="<?= $maxPrice > 0 ? e((string)$maxPrice) : '' ?>" placeholder="например 70000">
    </div>
    <button class="btn" type="submit">Применить</button>
</form>

<section class="grid">
    <?php foreach ($products as $product): ?>
        <article class="card">
            <?php if (!empty($product['image_path'])): ?>
                <img class="product-image" src="<?= BASE_URL . e($product['image_path']) ?>" alt="<?= e($product['name']) ?>">
            <?php endif; ?>
            <h3><?= e($product['name']) ?></h3>
            <p><strong>Бренд:</strong> <?= e($product['brand']) ?> · <strong>Категория:</strong> <?= e($product['category_name']) ?></p>
            <p><?= e($product['description']) ?></p>
            <p><strong>Рейтинг:</strong> <?= $product['avg_rating'] ? e((string)$product['avg_rating']) : 'нет оценок' ?> (<?= (int)$product['rating_count'] ?>)</p>
            <p><strong>Цена:</strong> <?= number_format((float)$product['price'], 0, '.', ' ') ?> ₽</p>
            <p><strong>Остаток:</strong> <?= (int)$product['stock'] ?> шт.</p>

            <?php if ($user): ?>
                <details>
                    <summary>Оставить отзыв</summary>
                    <form method="post" action="<?= BASE_URL ?>/index.php?page=catalog">
                        <input type="hidden" name="action" value="add_review">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <label>Оценка</label>
                        <select name="rating" required>
                            <option value="5">5</option>
                            <option value="4">4</option>
                            <option value="3">3</option>
                            <option value="2">2</option>
                            <option value="1">1</option>
                        </select>
                        <label>Комментарий</label>
                        <textarea name="comment" rows="3" required></textarea>
                        <button class="btn" type="submit">Отправить отзыв</button>
                    </form>
                </details>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</section>

<section class="card">
    <h2>Последние отзывы покупателей</h2>
    <div class="grid">
        <?php foreach ($reviews as $review): ?>
            <article class="card">
                <p><strong><?= e($review['product_name']) ?></strong> · <?= e($review['user_name']) ?></p>
                <p>Оценка: <?= (int)$review['rating'] ?>/5</p>
                <p><?= e($review['comment']) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>
