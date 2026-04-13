<?php
$category = (int)($_GET['category'] ?? 0);
$brand = trim($_GET['brand'] ?? '');
$maxPrice = (float)($_GET['max_price'] ?? 0);

$categories = db()->query('SELECT * FROM categories ORDER BY title')->fetchAll();

$sql = 'SELECT p.*, c.title AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE 1=1';
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
$sql .= ' ORDER BY p.created_at DESC';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$brands = db()->query('SELECT DISTINCT brand FROM products ORDER BY brand')->fetchAll();
?>

<h1>Каталог товаров</h1>
<p>В каталоге KKStore собраны популярные смартфоны, планшеты, ноутбуки и аксессуары от ведущих брендов. Используйте фильтры для быстрого подбора по категории, бренду и цене.</p>

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
            <h3><?= e($product['name']) ?></h3>
            <p><strong>Бренд:</strong> <?= e($product['brand']) ?> · <strong>Категория:</strong> <?= e($product['category_name']) ?></p>
            <p><?= e($product['description']) ?></p>
            <p><strong>Цена:</strong> <?= number_format((float)$product['price'], 0, '.', ' ') ?> ₽</p>
            <p><strong>Остаток:</strong> <?= (int)$product['stock'] ?> шт.</p>
        </article>
    <?php endforeach; ?>
</section>
