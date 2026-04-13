<?php
$success = flash('success');
$error = flash('error');
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e(APP_NAME) ?> — Магазин и сервис техники</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container nav-wrap">
        <a class="logo" href="<?= BASE_URL ?>/index.php?page=home">KKStore</a>
        <nav>
            <a href="<?= BASE_URL ?>/index.php?page=catalog">Каталог</a>
            <a href="<?= BASE_URL ?>/index.php?page=services">Услуги</a>
            <a href="<?= BASE_URL ?>/index.php?page=repairs">Ремонт</a>
            <a href="<?= BASE_URL ?>/index.php?page=accessories">Аксессуары</a>
            <a href="<?= BASE_URL ?>/index.php?page=news">Новости</a>
            <a href="<?= BASE_URL ?>/index.php?page=promotions">Акции</a>
            <a href="<?= BASE_URL ?>/index.php?page=faq">FAQ</a>
            <a href="<?= BASE_URL ?>/index.php?page=warranty">Гарантия</a>
            <a href="<?= BASE_URL ?>/index.php?page=tradein">Trade-in</a>
            <a href="<?= BASE_URL ?>/index.php?page=about">О нас</a>
            <a href="<?= BASE_URL ?>/index.php?page=contacts">Контакты</a>
        </nav>
        <div class="auth-links">
            <?php if ($user): ?>
                <?php if (!empty($user['avatar_path'])): ?>
                    <img src="<?= BASE_URL . e($user['avatar_path']) ?>" alt="avatar" class="avatar-mini">
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/index.php?page=dashboard">Личный кабинет</a>
                <form action="<?= BASE_URL ?>/index.php" method="post" class="inline-form">
                    <input type="hidden" name="action" value="logout">
                    <button class="link-btn" type="submit">Выйти</button>
                </form>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/index.php?page=login">Вход</a>
                <a href="<?= BASE_URL ?>/index.php?page=register">Регистрация</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="container">
    <?php if ($success): ?><div class="alert success"><?= e($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
