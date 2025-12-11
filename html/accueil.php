<h2>Coups de cœur</h2>
<div class="galerie-coeur">
    <a href="index.php?page=monuments"><img src="assets/img/rome_mini.jpg" class="thumb" alt="Rome"></a>
    <a href="index.php?page=villes"><img src="assets/img/venise_mini.jpg" class="thumb" alt="Venise"></a>
    <a href="index.php?page=map"><img src="assets/img/map_mini.jpg" class="thumb" alt="Carte"></a>
</div>
<hr>
<h2>Dernières News</h2>
<?php
    $stmt = $pdo->query("SELECT news_id, titre, resume, date_publication FROM News ORDER BY date_publication DESC LIMIT 3");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)):
?>
    <article class="news-item">
        <p class="date-pub">Date de publication : <?= e($row['date_publication']) ?></p>
        <h4><?= e($row['titre']) ?></h4>
        <p><?= e($row['resume']) ?></p>
        <a class="btn-detail" href="index.php?page=news_detail&id=<?= (int)$row['news_id'] ?>">Cliquez ici pour plus de détail</a>
    </article>
<?php endwhile; ?>
<div class="all-news-link"><a href="index.php?page=toutes_news">&gt;&gt; Toutes les news</a></div>
