<?php $captcha = createCaptchaChallenge('login'); ?>
<h1>Вход</h1>
<p>Для защиты аккаунтов от автоматических попыток входа используется усиленная CAPTCHA: символьный код и математическая проверка.</p>
<form method="post" action="<?= BASE_URL ?>/index.php?page=login">
    <input type="hidden" name="action" value="login">
    <label>Email</label>
    <input type="email" name="email" required>
    <label>Пароль</label>
    <input type="password" name="password" required>

    <h3>Сложная CAPTCHA</h3>
    <p><strong>Введите код:</strong> <span class="badge pending" style="font-size:16px"><?= e($captcha['code']) ?></span></p>
    <input type="text" name="captcha_code" required>
    <p><strong>Решите выражение:</strong> <?= e($captcha['question']) ?> = ?</p>
    <input type="text" name="captcha_math" required>

    <button class="btn" type="submit">Войти</button>
</form>
