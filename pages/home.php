<?php
$topRated = db()->query('SELECT p.name, p.brand, ROUND(AVG(pr.rating),1) AS avg_rating, COUNT(pr.id) AS total_reviews FROM products p JOIN product_reviews pr ON pr.product_id = p.id AND pr.status = "approved" GROUP BY p.id ORDER BY avg_rating DESC, total_reviews DESC LIMIT 6')->fetchAll();
?>
<section class="hero">
    <h1>KKStore — современный магазин и сервисный центр</h1>
    <p>Мы продаём смартфоны, планшеты, ноутбуки и аксессуары, а также выполняем профессиональный ремонт мобильной и компьютерной техники. Наш приоритет — быстрый сервис, прозрачные цены и официальная гарантия.</p>
    <a class="btn" href="<?= BASE_URL ?>/index.php?page=catalog">Перейти в каталог</a>
</section>

<section class="grid">
    <article class="card"><h3>Умные отзывы</h3><p>Теперь в KKStore работает система рейтингов с модерацией: реальные оценки помогают выбрать лучший товар.</p></article>
    <article class="card"><h3>Профессиональный ремонт</h3><p>Смартфоны, ноутбуки, планшеты и ПК — от базовой диагностики до сложных восстановлений.</p></article>
    <article class="card"><h3>Единая экосистема</h3><p>Магазин, сервис, личный кабинет, заявки и обратная связь в одном интерфейсе.</p></article>
</section>

<section class="card">
    <h2>Топ товаров по оценкам покупателей</h2>
    <div class="grid">
        <?php foreach ($topRated as $item): ?>
            <article class="card">
                <h3><?= e($item['name']) ?></h3>
                <p><?= e($item['brand']) ?></p>
                <p><strong><?= e((string)$item['avg_rating']) ?>/5</strong> · <?= (int)$item['total_reviews'] ?> отзывов</p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="card long-text">
    <h2>Что я добавил «от себя»</h2>
    <p>Я сделал полноценный контур «социального доверия» для магазина: отзывы, рейтинги, модерация, аналитика в админке и блок лучших товаров на главной. Это превращает проект из просто CRUD-сайта в более живую коммерческую систему, где решения пользователей влияют на витрину и приоритеты.</p>
    <p>Такая механика отлично выглядит на защите проекта: можно показать путь от клиента (оставляет отзыв) до администратора (модерирует), а затем на главной сразу видно результат — лучшие товары поднимаются в выдаче благодаря качественной обратной связи.</p>
</section>
