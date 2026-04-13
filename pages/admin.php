<?php
$tab = $_GET['tab'] ?? 'overview';
$allowedTabs = ['overview', 'products', 'users', 'repairs', 'reviews', 'content', 'requests'];
if (!in_array($tab, $allowedTabs, true)) {
    $tab = 'overview';
}

$stats = db()->query('SELECT (SELECT COUNT(*) FROM products) AS products_count, (SELECT COUNT(*) FROM users) AS users_count, (SELECT COUNT(*) FROM repair_requests WHERE status = "pending") AS pending_repairs, (SELECT COUNT(*) FROM product_reviews WHERE status = "pending") AS pending_reviews')->fetch();
$categories = db()->query('SELECT * FROM categories ORDER BY title')->fetchAll();
?>

<h1>Админ-панель (по отделам)</h1>
<p>Панель разделена на отделы: товары, пользователи, ремонт, отзывы, контент и заявки. В каждом разделе есть собственный фильтр.</p>

<div class="card">
    <a class="btn secondary" href="<?= BASE_URL ?>/index.php?page=admin&tab=overview">Обзор</a>
    <a class="btn secondary" href="<?= BASE_URL ?>/index.php?page=admin&tab=products">Товары</a>
    <a class="btn secondary" href="<?= BASE_URL ?>/index.php?page=admin&tab=users">Пользователи</a>
    <a class="btn secondary" href="<?= BASE_URL ?>/index.php?page=admin&tab=repairs">Ремонт</a>
    <a class="btn secondary" href="<?= BASE_URL ?>/index.php?page=admin&tab=reviews">Отзывы</a>
    <a class="btn secondary" href="<?= BASE_URL ?>/index.php?page=admin&tab=content">Контент</a>
    <a class="btn secondary" href="<?= BASE_URL ?>/index.php?page=admin&tab=requests">Заявки менеджеров</a>
</div>

<?php if ($tab === 'overview'): ?>
    <section class="grid">
        <article class="card"><h3>Товаров</h3><p><?= (int)$stats['products_count'] ?></p></article>
        <article class="card"><h3>Пользователей</h3><p><?= (int)$stats['users_count'] ?></p></article>
        <article class="card"><h3>Ожидают ремонт</h3><p><?= (int)$stats['pending_repairs'] ?></p></article>
        <article class="card"><h3>Ожидают отзывы</h3><p><?= (int)$stats['pending_reviews'] ?></p></article>
    </section>
<?php endif; ?>

<?php if ($tab === 'products'): ?>
    <?php
    $productCategory = (int)($_GET['product_category'] ?? 0);
    $productBrand = trim($_GET['product_brand'] ?? '');

    $sql = 'SELECT p.*, c.title AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE 1=1';
    $params = [];
    if ($productCategory > 0) { $sql .= ' AND p.category_id = :category'; $params['category'] = $productCategory; }
    if ($productBrand !== '') { $sql .= ' AND p.brand = :brand'; $params['brand'] = $productBrand; }
    $sql .= ' ORDER BY p.id DESC LIMIT 100';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    $brands = db()->query('SELECT DISTINCT brand FROM products ORDER BY brand')->fetchAll();
    ?>

    <form method="get" class="filters">
        <input type="hidden" name="page" value="admin">
        <input type="hidden" name="tab" value="products">
        <div>
            <label>Категория</label>
            <select name="product_category">
                <option value="0">Все</option>
                <?php foreach ($categories as $item): ?>
                    <option value="<?= $item['id'] ?>" <?= $productCategory === (int)$item['id'] ? 'selected' : '' ?>><?= e($item['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Бренд</label>
            <select name="product_brand">
                <option value="">Все</option>
                <?php foreach ($brands as $item): ?>
                    <option value="<?= e($item['brand']) ?>" <?= $productBrand === $item['brand'] ? 'selected' : '' ?>><?= e($item['brand']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn" type="submit">Фильтр</button>
    </form>

    <section class="card">
        <h3>Добавить товар</h3>
        <form method="post" action="<?= BASE_URL ?>/index.php?page=admin&tab=products" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_product">
            <div class="grid">
                <div><label>Название</label><input type="text" name="name" required></div>
                <div><label>Бренд</label><input type="text" name="brand" required></div>
                <div><label>Категория</label><select name="category_id" required><?php foreach ($categories as $category): ?><option value="<?= $category['id'] ?>"><?= e($category['title']) ?></option><?php endforeach; ?></select></div>
                <div><label>Цена</label><input type="number" name="price" step="0.01" required></div>
                <div><label>Остаток</label><input type="number" name="stock" required></div>
                <div><label>Фото товара</label><input type="file" name="product_image" accept="image/png,image/jpeg,image/webp"></div>
            </div>
            <label>Описание</label>
            <textarea name="description" rows="4" required></textarea>
            <label><input type="checkbox" name="is_featured"> Рекомендуемый товар</label>
            <button class="btn" type="submit">Сохранить</button>
        </form>
    </section>

    <section class="card">
        <h3>Список товаров</h3>
        <table class="table">
            <thead><tr><th>ID</th><th>Товар</th><th>Категория</th><th>Цена</th><th>Остаток</th></tr></thead>
            <tbody><?php foreach ($products as $item): ?><tr><td><?= $item['id'] ?></td><td><?= e($item['name']) ?> (<?= e($item['brand']) ?>)</td><td><?= e($item['category_name']) ?></td><td><?= number_format((float)$item['price'],0,'.',' ') ?> ₽</td><td><?= (int)$item['stock'] ?></td></tr><?php endforeach; ?></tbody>
        </table>
    </section>
<?php endif; ?>

<?php if ($tab === 'users'): ?>
    <?php
    $roleFilter = $_GET['role'] ?? '';
    $search = trim($_GET['search'] ?? '');
    $sql = 'SELECT * FROM users WHERE 1=1';
    $params = [];
    if (in_array($roleFilter, ['customer', 'manager', 'admin'], true)) { $sql .= ' AND role = :role'; $params['role'] = $roleFilter; }
    if ($search !== '') { $sql .= ' AND (name LIKE :search OR email LIKE :search)'; $params['search'] = '%' . $search . '%'; }
    $sql .= ' ORDER BY id DESC LIMIT 200';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll();
    ?>

    <form method="get" class="filters">
        <input type="hidden" name="page" value="admin">
        <input type="hidden" name="tab" value="users">
        <div>
            <label>Роль</label>
            <select name="role">
                <option value="">Все</option>
                <option value="customer" <?= $roleFilter === 'customer' ? 'selected' : '' ?>>customer</option>
                <option value="manager" <?= $roleFilter === 'manager' ? 'selected' : '' ?>>manager</option>
                <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>admin</option>
            </select>
        </div>
        <div>
            <label>Поиск (имя или email)</label>
            <input type="text" name="search" value="<?= e($search) ?>">
        </div>
        <button class="btn" type="submit">Фильтр</button>
    </form>

    <section class="card">
        <h3>Пользователи</h3>
        <table class="table">
            <thead><tr><th>ID</th><th>Имя</th><th>Email</th><th>Роль</th><th>Изменить роль</th></tr></thead>
            <tbody>
            <?php foreach ($users as $item): ?>
                <tr>
                    <td><?= $item['id'] ?></td>
                    <td><?= e($item['name']) ?></td>
                    <td><?= e($item['email']) ?></td>
                    <td><?= e($item['role']) ?></td>
                    <td>
                        <form method="post" action="<?= BASE_URL ?>/index.php?page=admin&tab=users">
                            <input type="hidden" name="action" value="update_user_role">
                            <input type="hidden" name="user_id" value="<?= $item['id'] ?>">
                            <select name="role">
                                <option value="customer">customer</option>
                                <option value="manager">manager</option>
                                <option value="admin">admin</option>
                            </select>
                            <button class="btn" type="submit">Сохранить</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
<?php endif; ?>

<?php if ($tab === 'repairs'): ?>
    <?php
    $status = $_GET['repair_status'] ?? '';
    $sql = 'SELECT rr.*, u.name AS user_name FROM repair_requests rr JOIN users u ON u.id = rr.user_id WHERE 1=1';
    $params = [];
    if (in_array($status, ['pending','approved','rejected'], true)) { $sql .= ' AND rr.status = :status'; $params['status'] = $status; }
    $sql .= ' ORDER BY rr.id DESC';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $repairRequests = $stmt->fetchAll();
    ?>

    <form method="get" class="filters">
        <input type="hidden" name="page" value="admin"><input type="hidden" name="tab" value="repairs">
        <div><label>Статус</label><select name="repair_status"><option value="">Все</option><option value="pending" <?= $status==='pending'?'selected':'' ?>>pending</option><option value="approved" <?= $status==='approved'?'selected':'' ?>>approved</option><option value="rejected" <?= $status==='rejected'?'selected':'' ?>>rejected</option></select></div>
        <button class="btn" type="submit">Фильтр</button>
    </form>

    <section class="card">
        <h3>Заявки на ремонт</h3>
        <table class="table">
            <thead><tr><th>Клиент</th><th>Устройство</th><th>Статус</th><th>Решение</th></tr></thead>
            <tbody>
            <?php foreach ($repairRequests as $item): ?>
                <tr>
                    <td><?= e($item['user_name']) ?></td>
                    <td><?= e($item['device_type']) ?> <?= e($item['brand']) ?> <?= e($item['model']) ?></td>
                    <td><span class="badge <?= e($item['status']) ?>"><?= e($item['status']) ?></span></td>
                    <td>
                        <?php if ($item['status'] === 'pending'): ?>
                            <form method="post" action="<?= BASE_URL ?>/index.php?page=admin&tab=repairs">
                                <input type="hidden" name="action" value="repair_decision">
                                <input type="hidden" name="repair_id" value="<?= $item['id'] ?>">
                                <select name="decision" required><option value="approve">Принять</option><option value="reject">Отклонить</option></select>
                                <textarea name="reason" rows="2" required placeholder="Причина обязательна"></textarea>
                                <button class="btn" type="submit">Сохранить</button>
                            </form>
                        <?php else: ?>
                            <?= e($item['decision_reason'] ?? '—') ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
<?php endif; ?>

<?php if ($tab === 'reviews'): ?>
    <?php
    $reviewStatus = $_GET['review_status'] ?? '';
    $sql = 'SELECT pr.*, p.name AS product_name, u.name AS user_name FROM product_reviews pr JOIN products p ON p.id = pr.product_id JOIN users u ON u.id = pr.user_id WHERE 1=1';
    $params = [];
    if (in_array($reviewStatus, ['pending','approved','rejected'], true)) { $sql .= ' AND pr.status = :status'; $params['status'] = $reviewStatus; }
    $sql .= ' ORDER BY pr.id DESC';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $reviewRequests = $stmt->fetchAll();
    ?>

    <form method="get" class="filters">
        <input type="hidden" name="page" value="admin"><input type="hidden" name="tab" value="reviews">
        <div><label>Статус отзыва</label><select name="review_status"><option value="">Все</option><option value="pending" <?= $reviewStatus==='pending'?'selected':'' ?>>pending</option><option value="approved" <?= $reviewStatus==='approved'?'selected':'' ?>>approved</option><option value="rejected" <?= $reviewStatus==='rejected'?'selected':'' ?>>rejected</option></select></div>
        <button class="btn" type="submit">Фильтр</button>
    </form>

    <section class="card">
        <h3>Отзывы</h3>
        <table class="table">
            <thead><tr><th>Покупатель</th><th>Товар</th><th>Оценка</th><th>Статус</th><th>Модерация</th></tr></thead>
            <tbody>
            <?php foreach ($reviewRequests as $item): ?>
                <tr>
                    <td><?= e($item['user_name']) ?></td>
                    <td><?= e($item['product_name']) ?></td>
                    <td><?= (int)$item['rating'] ?>/5</td>
                    <td><span class="badge <?= e($item['status']) ?>"><?= e($item['status']) ?></span></td>
                    <td>
                        <?php if ($item['status'] === 'pending'): ?>
                            <form method="post" action="<?= BASE_URL ?>/index.php?page=admin&tab=reviews">
                                <input type="hidden" name="action" value="review_decision">
                                <input type="hidden" name="review_id" value="<?= $item['id'] ?>">
                                <select name="decision" required><option value="approve">Одобрить</option><option value="reject">Отклонить</option></select>
                                <textarea name="admin_note" rows="2" required placeholder="Комментарий администратора"></textarea>
                                <button class="btn" type="submit">Сохранить</button>
                            </form>
                        <?php else: ?>
                            <?= e($item['admin_note'] ?? '—') ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
<?php endif; ?>

<?php if ($tab === 'content'): ?>
    <?php
    $newsSearch = trim($_GET['news_search'] ?? '');
    $sql = 'SELECT * FROM news WHERE 1=1';
    $params = [];
    if ($newsSearch !== '') { $sql .= ' AND title LIKE :q'; $params['q'] = '%' . $newsSearch . '%'; }
    $sql .= ' ORDER BY id DESC LIMIT 50';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $news = $stmt->fetchAll();
    ?>

    <form method="get" class="filters">
        <input type="hidden" name="page" value="admin"><input type="hidden" name="tab" value="content">
        <div><label>Поиск новостей</label><input type="text" name="news_search" value="<?= e($newsSearch) ?>"></div>
        <button class="btn" type="submit">Фильтр</button>
    </form>

    <section class="grid">
        <article class="card">
            <h3>Добавить категорию</h3>
            <form method="post" action="<?= BASE_URL ?>/index.php?page=admin&tab=content">
                <input type="hidden" name="action" value="add_category">
                <label>Название</label><input type="text" name="title" required>
                <button class="btn" type="submit">Добавить</button>
            </form>
        </article>
        <article class="card">
            <h3>Добавить новость</h3>
            <form method="post" action="<?= BASE_URL ?>/index.php?page=admin&tab=content">
                <input type="hidden" name="action" value="add_news">
                <label>Заголовок</label><input type="text" name="title" required>
                <label>Текст</label><textarea name="content" rows="4" required></textarea>
                <button class="btn" type="submit">Опубликовать</button>
            </form>
        </article>
    </section>

    <section class="card">
        <h3>Новости</h3>
        <ul><?php foreach ($news as $item): ?><li><?= e($item['title']) ?></li><?php endforeach; ?></ul>
    </section>
<?php endif; ?>

<?php if ($tab === 'requests'): ?>
    <?php
    $requestStatus = $_GET['request_status'] ?? '';
    $sql = 'SELECT r.*, u.name AS manager_name, c.title AS category_name FROM product_requests r JOIN users u ON u.id = r.manager_id JOIN categories c ON c.id = r.category_id WHERE 1=1';
    $params = [];
    if (in_array($requestStatus, ['pending','approved','rejected'], true)) { $sql .= ' AND r.status = :status'; $params['status'] = $requestStatus; }
    $sql .= ' ORDER BY r.id DESC';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $requests = $stmt->fetchAll();
    ?>

    <form method="get" class="filters">
        <input type="hidden" name="page" value="admin"><input type="hidden" name="tab" value="requests">
        <div><label>Статус заявки</label><select name="request_status"><option value="">Все</option><option value="pending" <?= $requestStatus==='pending'?'selected':'' ?>>pending</option><option value="approved" <?= $requestStatus==='approved'?'selected':'' ?>>approved</option><option value="rejected" <?= $requestStatus==='rejected'?'selected':'' ?>>rejected</option></select></div>
        <button class="btn" type="submit">Фильтр</button>
    </form>

    <section class="card">
        <h3>Заявки менеджеров</h3>
        <table class="table">
            <thead><tr><th>Менеджер</th><th>Товар</th><th>Категория</th><th>Статус</th><th>Действие</th></tr></thead>
            <tbody>
            <?php foreach ($requests as $item): ?>
                <tr>
                    <td><?= e($item['manager_name']) ?></td>
                    <td><?= e($item['name']) ?></td>
                    <td><?= e($item['category_name']) ?></td>
                    <td><span class="badge <?= e($item['status']) ?>"><?= e($item['status']) ?></span></td>
                    <td>
                        <?php if ($item['status'] === 'pending'): ?>
                            <form class="inline-form" method="post" action="<?= BASE_URL ?>/index.php?page=admin&tab=requests"><input type="hidden" name="action" value="request_decision"><input type="hidden" name="request_id" value="<?= $item['id'] ?>"><input type="hidden" name="decision" value="approve"><button class="btn" type="submit">Принять</button></form>
                            <form class="inline-form" method="post" action="<?= BASE_URL ?>/index.php?page=admin&tab=requests"><input type="hidden" name="action" value="request_decision"><input type="hidden" name="request_id" value="<?= $item['id'] ?>"><input type="hidden" name="decision" value="reject"><button class="btn danger" type="submit">Отклонить</button></form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
<?php endif; ?>
