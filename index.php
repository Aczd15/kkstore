<?php

declare(strict_types=1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

$page = currentPath();
$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'register') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = ($_POST['role'] ?? 'customer') === 'manager' ? 'manager' : 'customer';

        if ($name === '' || $email === '' || $password === '') {
            flash('error', 'Заполните все поля регистрации.');
            redirect('/index.php?page=register');
        }

        $exists = db()->prepare('SELECT id FROM users WHERE email = :email');
        $exists->execute(['email' => $email]);

        if ($exists->fetch()) {
            flash('error', 'Пользователь с такой почтой уже существует.');
            redirect('/index.php?page=register');
        }

        $stmt = db()->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, :role)');
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
        ]);

        flash('success', 'Регистрация успешна. Войдите в аккаунт.');
        redirect('/index.php?page=login');
    }

    if ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = db()->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $candidate = $stmt->fetch();

        if (!$candidate || !password_verify($password, $candidate['password_hash'])) {
            flash('error', 'Неверный email или пароль.');
            redirect('/index.php?page=login');
        }

        $_SESSION['user_id'] = (int)$candidate['id'];
        flash('success', 'Вы успешно вошли.');
        redirect('/index.php?page=dashboard');
    }

    if ($action === 'logout') {
        session_destroy();
        session_start();
        flash('success', 'Вы вышли из аккаунта.');
        redirect('/index.php?page=home');
    }

    if ($action === 'manager_request') {
        requireRole('manager');

        $stmt = db()->prepare(
            'INSERT INTO product_requests (manager_id, name, brand, description, price, category_id, status) VALUES (:manager_id, :name, :brand, :description, :price, :category_id, :status)'
        );
        $stmt->execute([
            'manager_id' => $user['id'],
            'name' => trim($_POST['name'] ?? ''),
            'brand' => trim($_POST['brand'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'price' => (float)($_POST['price'] ?? 0),
            'category_id' => (int)($_POST['category_id'] ?? 1),
            'status' => 'pending',
        ]);

        flash('success', 'Заявка отправлена администратору.');
        redirect('/index.php?page=manager');
    }

    if ($action === 'add_category') {
        requireRole('admin');

        $title = trim($_POST['title'] ?? '');
        if ($title !== '') {
            $stmt = db()->prepare('INSERT INTO categories (title, slug) VALUES (:title, :slug)');
            $stmt->execute([
                'title' => $title,
                'slug' => strtolower(preg_replace('/\s+/', '-', $title)),
            ]);
            flash('success', 'Категория добавлена.');
        }
        redirect('/index.php?page=admin');
    }

    if ($action === 'add_news') {
        requireRole('admin');

        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        if ($title !== '' && $content !== '') {
            $stmt = db()->prepare('INSERT INTO news (title, content) VALUES (:title, :content)');
            $stmt->execute(['title' => $title, 'content' => $content]);
            flash('success', 'Новость добавлена.');
        }
        redirect('/index.php?page=admin');
    }

    if ($action === 'add_product') {
        requireRole('admin');

        $stmt = db()->prepare(
            'INSERT INTO products (name, brand, description, price, category_id, stock, is_featured) VALUES (:name, :brand, :description, :price, :category_id, :stock, :is_featured)'
        );
        $stmt->execute([
            'name' => trim($_POST['name'] ?? ''),
            'brand' => trim($_POST['brand'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'price' => (float)($_POST['price'] ?? 0),
            'category_id' => (int)($_POST['category_id'] ?? 1),
            'stock' => (int)($_POST['stock'] ?? 0),
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        ]);

        flash('success', 'Товар добавлен.');
        redirect('/index.php?page=admin');
    }

    if ($action === 'request_decision') {
        requireRole('admin');

        $requestId = (int)($_POST['request_id'] ?? 0);
        $decision = ($_POST['decision'] ?? 'reject') === 'approve' ? 'approved' : 'rejected';

        $stmt = db()->prepare('UPDATE product_requests SET status = :status WHERE id = :id');
        $stmt->execute(['status' => $decision, 'id' => $requestId]);

        if ($decision === 'approved') {
            $source = db()->prepare('SELECT * FROM product_requests WHERE id = :id');
            $source->execute(['id' => $requestId]);
            $req = $source->fetch();
            if ($req) {
                $insert = db()->prepare(
                    'INSERT INTO products (name, brand, description, price, category_id, stock, is_featured) VALUES (:name, :brand, :description, :price, :category_id, :stock, 0)'
                );
                $insert->execute([
                    'name' => $req['name'],
                    'brand' => $req['brand'],
                    'description' => $req['description'],
                    'price' => $req['price'],
                    'category_id' => $req['category_id'],
                    'stock' => 10,
                ]);
            }
        }

        flash('success', 'Статус заявки обновлен.');
        redirect('/index.php?page=admin');
    }
}

$allowedPages = [
    'home', 'catalog', 'services', 'about', 'news', 'contacts', 'login', 'register',
    'dashboard', 'admin', 'manager', 'repairs', 'accessories'
];

if (!in_array($page, $allowedPages, true)) {
    $page = 'home';
}

if ($page === 'dashboard') {
    requireAuth();
}
if ($page === 'admin') {
    requireRole('admin');
}
if ($page === 'manager') {
    requireRole('manager');
}

require __DIR__ . '/templates/header.php';
require __DIR__ . '/pages/' . $page . '.php';
require __DIR__ . '/templates/footer.php';
