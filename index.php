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
        $captchaCode = $_POST['captcha_code'] ?? '';
        $captchaMath = $_POST['captcha_math'] ?? '';

        if (!verifyCaptcha('register', $captchaCode, $captchaMath)) {
            flash('error', 'CAPTCHA не пройдена. Проверьте код и математическое выражение.');
            redirect('/index.php?page=register');
        }

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

        $avatarPath = saveUploadedImage($_FILES['avatar'] ?? [], 'avatars');

        $stmt = db()->prepare('INSERT INTO users (name, email, password_hash, role, avatar_path) VALUES (:name, :email, :password_hash, :role, :avatar_path)');
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
            'avatar_path' => $avatarPath,
        ]);

        flash('success', 'Регистрация успешна. Войдите в аккаунт.');
        redirect('/index.php?page=login');
    }

    if ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $captchaCode = $_POST['captcha_code'] ?? '';
        $captchaMath = $_POST['captcha_math'] ?? '';

        if (!verifyCaptcha('login', $captchaCode, $captchaMath)) {
            flash('error', 'CAPTCHA не пройдена. Попробуйте еще раз.');
            redirect('/index.php?page=login');
        }

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

    if ($action === 'update_avatar') {
        requireAuth();
        $avatarPath = saveUploadedImage($_FILES['avatar'] ?? [], 'avatars');
        if ($avatarPath === null) {
            flash('error', 'Не удалось загрузить аватар. Поддерживаются jpg/png/webp до 4MB.');
            redirect('/index.php?page=dashboard');
        }

        $stmt = db()->prepare('UPDATE users SET avatar_path = :avatar_path WHERE id = :id');
        $stmt->execute(['avatar_path' => $avatarPath, 'id' => $user['id']]);

        flash('success', 'Аватар обновлен.');
        redirect('/index.php?page=dashboard');
    }

    if ($action === 'change_password') {
        requireAuth();
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $repeatPassword = $_POST['repeat_password'] ?? '';

        $stmt = db()->prepare('SELECT password_hash FROM users WHERE id = :id');
        $stmt->execute(['id' => $user['id']]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($currentPassword, $row['password_hash'])) {
            flash('error', 'Текущий пароль неверный.');
            redirect('/index.php?page=dashboard');
        }

        if (strlen($newPassword) < 8 || $newPassword !== $repeatPassword) {
            flash('error', 'Новый пароль должен быть минимум 8 символов и совпадать в обоих полях.');
            redirect('/index.php?page=dashboard');
        }

        $update = db()->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id');
        $update->execute([
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
            'id' => $user['id'],
        ]);

        flash('success', 'Пароль успешно изменен.');
        redirect('/index.php?page=dashboard');
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


    if ($action === 'add_review') {
        requireAuth();

        $productId = (int)($_POST['product_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');

        if ($productId <= 0 || $rating < 1 || $rating > 5 || $comment === '') {
            flash('error', 'Заполните корректно рейтинг и текст отзыва.');
            redirect('/index.php?page=catalog');
        }

        $stmt = db()->prepare('INSERT INTO product_reviews (product_id, user_id, rating, comment, status) VALUES (:product_id, :user_id, :rating, :comment, :status)');
        $stmt->execute([
            'product_id' => $productId,
            'user_id' => $user['id'],
            'rating' => $rating,
            'comment' => $comment,
            'status' => 'pending',
        ]);

        flash('success', 'Спасибо! Отзыв отправлен на модерацию.');
        redirect('/index.php?page=catalog');
    }

    if ($action === 'create_repair_request') {
        requireAuth();

        $stmt = db()->prepare('INSERT INTO repair_requests (user_id, device_type, brand, model, issue_description, contact_phone, preferred_date, status) VALUES (:user_id, :device_type, :brand, :model, :issue_description, :contact_phone, :preferred_date, :status)');
        $stmt->execute([
            'user_id' => $user['id'],
            'device_type' => trim($_POST['device_type'] ?? ''),
            'brand' => trim($_POST['brand'] ?? ''),
            'model' => trim($_POST['model'] ?? ''),
            'issue_description' => trim($_POST['issue_description'] ?? ''),
            'contact_phone' => trim($_POST['contact_phone'] ?? ''),
            'preferred_date' => $_POST['preferred_date'] !== '' ? $_POST['preferred_date'] : null,
            'status' => 'pending',
        ]);

        flash('success', 'Заявка на ремонт отправлена. Администратор рассмотрит ее в ближайшее время.');
        redirect('/index.php?page=repairs');
    }


    if ($action === 'update_user_role') {
        requireRole('admin');

        $userId = (int)($_POST['user_id'] ?? 0);
        $role = $_POST['role'] ?? 'customer';
        $allowedRoles = ['customer', 'manager', 'admin'];

        if ($userId <= 0 || !in_array($role, $allowedRoles, true)) {
            flash('error', 'Некорректные данные для обновления роли пользователя.');
            redirect('/index.php?page=admin&tab=users');
        }

        $stmt = db()->prepare('UPDATE users SET role = :role WHERE id = :id');
        $stmt->execute(['role' => $role, 'id' => $userId]);

        flash('success', 'Роль пользователя обновлена.');
        redirect('/index.php?page=admin&tab=users');
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

        $productImage = saveUploadedImage($_FILES['product_image'] ?? [], 'products');

        $stmt = db()->prepare(
            'INSERT INTO products (name, brand, description, image_path, price, category_id, stock, is_featured) VALUES (:name, :brand, :description, :image_path, :price, :category_id, :stock, :is_featured)'
        );
        $stmt->execute([
            'name' => trim($_POST['name'] ?? ''),
            'brand' => trim($_POST['brand'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'image_path' => $productImage,
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
                    'INSERT INTO products (name, brand, description, image_path, price, category_id, stock, is_featured) VALUES (:name, :brand, :description, NULL, :price, :category_id, :stock, 0)'
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


    if ($action === 'review_decision') {
        requireRole('admin');

        $reviewId = (int)($_POST['review_id'] ?? 0);
        $decision = ($_POST['decision'] ?? '') === 'approve' ? 'approved' : 'rejected';
        $note = trim($_POST['admin_note'] ?? '');

        if ($note === '') {
            flash('error', 'Укажите комментарий администратора для модерации отзыва.');
            redirect('/index.php?page=admin');
        }

        $stmt = db()->prepare('UPDATE product_reviews SET status = :status, admin_note = :admin_note WHERE id = :id');
        $stmt->execute([
            'status' => $decision,
            'admin_note' => $note,
            'id' => $reviewId,
        ]);

        flash('success', 'Решение по отзыву сохранено.');
        redirect('/index.php?page=admin');
    }

    if ($action === 'repair_decision') {
        requireRole('admin');

        $repairId = (int)($_POST['repair_id'] ?? 0);
        $decision = ($_POST['decision'] ?? '') === 'approve' ? 'approved' : 'rejected';
        $reason = trim($_POST['reason'] ?? '');

        if ($reason === '') {
            flash('error', 'Для решения по ремонту обязательно укажите причину.');
            redirect('/index.php?page=admin');
        }

        $stmt = db()->prepare('UPDATE repair_requests SET status = :status, decision_reason = :decision_reason WHERE id = :id');
        $stmt->execute([
            'status' => $decision,
            'decision_reason' => $reason,
            'id' => $repairId,
        ]);

        flash('success', 'Решение по заявке на ремонт сохранено.');
        redirect('/index.php?page=admin');
    }
}

$allowedPages = [
    'home', 'catalog', 'services', 'about', 'news', 'contacts', 'login', 'register',
    'dashboard', 'admin', 'manager', 'repairs', 'accessories', 'promotions', 'faq', 'warranty', 'tradein'
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
