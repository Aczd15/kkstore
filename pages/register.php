<h1>Регистрация</h1>
<p>Вы можете зарегистрироваться как клиент или как менеджер магазина (для подачи заявок на новые товары).</p>
<form method="post" action="<?= BASE_URL ?>/index.php?page=register">
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
    <button class="btn" type="submit">Создать аккаунт</button>
</form>
