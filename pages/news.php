<?php $items = db()->query('SELECT * FROM news ORDER BY created_at DESC')->fetchAll(); ?>
<h1>Новости и обновления</h1>
<p>Следите за новинками каталога, акциями на ремонт и полезными рекомендациями по уходу за техникой.</p>

<section class="grid">
    <?php foreach ($items as $news): ?>
        <article class="card">
            <h3><?= e($news['title']) ?></h3>
            <p><?= nl2br(e($news['content'])) ?></p>
            <small><?= e($news['created_at']) ?></small>
        </article>
    <?php endforeach; ?>
</section>
