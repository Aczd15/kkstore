<h1>Ремонт техники</h1>
<p>Мы принимаем в ремонт телефоны, компьютеры, планшеты, мониторы, игровые консоли и другую цифровую электронику (кроме крупной бытовой техники). Работаем с частными клиентами и организациями.</p>

<div class="long-text card">
    <h3>Как проходит ремонт</h3>
    <p>1) Вы оставляете заявку через сайт или приносите устройство в сервис. 2) Инженер проводит диагностику и фиксирует неисправность. 3) Мы согласовываем с вами стоимость и сроки. 4) После ремонта вы получаете устройство с гарантией и рекомендациями по эксплуатации.</p>
    <p>В большинстве случаев ремонт смартфонов выполняется в день обращения, если деталь есть в наличии. Для редких моделей сроки могут увеличиваться, но вы всегда видите статус в личном кабинете.</p>
    <p>Дополнительно мы предлагаем профилактику: чистка разъемов, замена термопасты, профилактическая чистка системы охлаждения ноутбуков, диагностика аккумулятора и обновление ПО.</p>
</div>

<?php if ($user): ?>
<form method="post" action="<?= BASE_URL ?>/index.php?page=repairs">
    <input type="hidden" name="action" value="create_repair_request">
    <h3>Запись на ремонт</h3>
    <label>Тип устройства</label>
    <select name="device_type" required>
        <option value="Телефон">Телефон</option>
        <option value="Планшет">Планшет</option>
        <option value="Ноутбук">Ноутбук</option>
        <option value="Компьютер">Компьютер</option>
        <option value="Другое">Другое</option>
    </select>
    <label>Бренд</label>
    <input type="text" name="brand" required>
    <label>Модель</label>
    <input type="text" name="model" required>
    <label>Описание неисправности</label>
    <textarea name="issue_description" rows="4" required></textarea>
    <label>Контактный телефон</label>
    <input type="text" name="contact_phone" required>
    <label>Желаемая дата визита</label>
    <input type="date" name="preferred_date">
    <button class="btn" type="submit">Отправить заявку</button>
</form>
<?php else: ?>
<div class="card"><p>Чтобы записаться на ремонт, <a href="<?= BASE_URL ?>/index.php?page=login">войдите</a> или <a href="<?= BASE_URL ?>/index.php?page=register">зарегистрируйтесь</a>.</p></div>
<?php endif; ?>

<table class="table">
    <thead><tr><th>Услуга</th><th>Срок</th><th>Цена от</th></tr></thead>
    <tbody>
    <tr><td>Замена экрана смартфона</td><td>1-2 часа</td><td>2 500 ₽</td></tr>
    <tr><td>Замена аккумулятора</td><td>40 минут</td><td>1 200 ₽</td></tr>
    <tr><td>Ремонт разъёма зарядки</td><td>2-4 часа</td><td>1 800 ₽</td></tr>
    <tr><td>Чистка ноутбука и замена термопасты</td><td>1 день</td><td>2 000 ₽</td></tr>
    <tr><td>Восстановление после попадания влаги</td><td>1-3 дня</td><td>3 500 ₽</td></tr>
    </tbody>
</table>
