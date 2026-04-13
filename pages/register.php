<?php $captcha = createCaptchaChallenge('register'); ?>
<h1>Регистрация</h1>
<p>Вы можете зарегистрироваться как клиент или как менеджер магазина (для подачи заявок на новые товары). После регистрации вам будут доступны личный кабинет, история заявок и персональные уведомления.</p>
<form method="post" action="<?= BASE_URL ?>/index.php?page=register" enctype="multipart/form-data">
    <input type="hidden" name="action" value="register">
    <label>Имя</label>
    <input type="text" name="name" required>
    <label>Email</label>
    <input type="email" name="email" required>
    <label>Пароль</label>
    <input type="password" name="password" required>
    <label>Роль</label>
    <select name="role">
        <option value="customer">Клиент</option>
        <option value="manager">Менеджер</option>
    </select>
    <label>Аватар (jpg/png/webp, до 4MB)</label>
    <input type="file" name="avatar" accept="image/png,image/jpeg,image/webp">

    <h3>Сложная CAPTCHA</h3>
    <p><strong>Введите код:</strong> <span class="badge pending" style="font-size:16px"><?= e($captcha['code']) ?></span></p>
    <input type="text" name="captcha_code" required placeholder="Введите код символов">
    <p><strong>Решите выражение:</strong> <?= e($captcha['question']) ?> = ?</p>
    <input type="text" name="captcha_math" required placeholder="Введите результат">

    <button class="btn" type="submit">Создать аккаунт</button>
</form>
