<h1>Вход</h1>
<form method="post" action="<?= BASE_URL ?>/index.php?page=login">
    <input type="hidden" name="action" value="login">
    <label>Email</label>
    <input type="email" name="email" required>
    <label>Пароль</label>
    <input type="password" name="password" required>
    <button class="btn" type="submit">Войти</button>
</form>
