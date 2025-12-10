<?php
// index.php - Front controller (amélioré)
// Minimal changes to keep structure but improved security + CSRF + output escaping

session_start();
require __DIR__ . '/db.php';

// CSRF token helper
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
}
function csrf_field(){
    return '<input type="hidden" name="csrf_token" value="'.htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8').'">';
}

function e($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

// --- Traitement Newsletter (Partie Dynamique) ---
$msg_news = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_newsletter'])) {
    // CSRF check
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $msg_news = "Requête invalide (token).";
    } else {
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($nom !== '' && $prenom !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $check = $pdo->prepare("SELECT 1 FROM Internaute WHERE email = ? LIMIT 1");
            $check->execute([$email]);
            if (!$check->fetch()) {
                $stmt = $pdo->prepare("INSERT INTO Internaute (nom, prenom, email) VALUES (?, ?, ?)");
                $stmt->execute([$nom, $prenom, $email]);
                $msg_news = "Inscription réussie !";
            } else {
                $msg_news = "Email déjà inscrit.";
            }
        } else {
            $msg_news = "Champs invalides.";
        }
    }
}
// --- Traitement Contact (Mode Débuggage) ---
$msg_contact_etat = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_contact'])) {
    
    // 1. On récupère les données du formulaire
    $nom = trim($_POST['cnom'] ?? '');
    $email = trim($_POST['cemail'] ?? '');
    $message = trim($_POST['cmsg'] ?? '');

    
    if ($nom === '' || $email === '' || $message === '') {
        $msg_contact_etat = "<p style='color:red; font-weight:bold;'>Erreur : Un des champs est vide.</p>";
    } 
    else {
        
        try {
            
            $stmt = $pdo->prepare("INSERT INTO Contact (nom, email, message) VALUES (?, ?, ?)");
            $stmt->execute([$nom, $email, $message]);
            
            $msg_contact_etat = "<p style='color:green; font-weight:bold;'>Succès : Message enregistré !</p>";
            
        } catch (PDOException $e) {
            // C'est ICI que l'erreur va s'afficher
            $msg_contact_etat = "<p style='color:red; font-weight:bold; background:white; padding:10px; border:2px solid red;'>ERREUR SQL : " . $e->getMessage() . "</p>";
        }
    }
}

// Routing
$page = $_GET['page'] ?? 'accueil';
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Italie - Voyage et Culture</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="site-container">
    <header class="site-header">
        <div class="header-inner">
            <h1>Italie — Voyage &amp; Culture</h1>
            <p class="tagline">Découvrez les trésors d'Italie</p>
        </div>
    </header>

    <nav class="menu-horizontal-cadre">
        <ul>
            <li><a href="index.php?page=accueil">Accueil</a></li>
            <li><a href="index.php?page=plan">Plan de site</a></li>
            <li><a href="index.php?page=quisommesnous">Qui sommes-nous ?</a></li>
            <li><a href="index.php?page=contact">Contact</a></li>
        </ul>
    </nav>

    <div class="contenu-principal">
        <aside class="col-gauche">
            <div class="menu-vertical-item">
                <h3>Monuments</h3>
                <div class="contenu-menu"><a href="index.php?page=monuments">Sites et Monuments</a></div>
            </div>
            <div class="menu-vertical-item">
                <h3>Villes</h3>
                <div class="contenu-menu"><a href="index.php?page=villes">Index des villes</a></div>
            </div>
            <div class="menu-vertical-item">
                <h3>Carte</h3>
                <div class="contenu-menu"><a href="index.php?page=map">Google Map</a></div>
            </div>
            <div class="menu-vertical-item">
                <h3>Liens Utiles</h3>
                <div class="contenu-menu"><a href="index.php?page=liens">Administrations</a></div>
            </div>
        </aside>

        <section class="col-centre">
            <?php switch($page):
                case 'accueil': ?>
                    <h2>Coups de cœur</h2>
                    <div class="galerie-coeur">
                        <a href="index.php?page=monuments"><img src="img/rome_mini.jpg" class="thumb" alt="Rome"></a>
                        <a href="index.php?page=villes"><img src="img/venise_mini.jpg" class="thumb" alt="Venise"></a>
                        <a href="index.php?page=map"><img src="img/map_mini.jpg" class="thumb" alt="Carte"></a>
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
                    <div class="all-news-link"><a href="index.php?page=toutes_news">>> Toutes les news</a></div>
                <?php break; ?>

                <?php case 'plan': ?>
                    <h2>Plan du site</h2>
                    <ul><li>Accueil</li><li>Villes</li><li>Monuments</li><li>Contact</li></ul>
                <?php break; ?>

                <?php case 'quisommesnous': ?>
                    <h2>Qui sommes-nous ?</h2>
                    <div class="card">
                        <p><strong>Binôme :</strong></p>
                        <ul>
                            <li>Nom : ELHAMMOUMI</li>
                            <li>Prénom : Youssef</li>
                            <li>CNE : N14035734</li>
                            <li>Email : youssef.elhammoumi@usmba.ac.ma</li>



                            <li>Nom :el gnaoui </li>
                            <li>Prénom : yahya</li>
                            <li>CNE : 13234654</li>
                            <li>Email : yahya.elgnaoui@usmba.ac.ma</li>
                        </ul>
                        <img src="img/etudiant.jpg" alt="Photo Étudiant" class="mini-photo">
                        <img src="img/etudiant1.jpg" alt="Photo Étudiant" class="mini-photo">
                    </div>
                <?php break; ?>

                <?php case 'contact': ?>
                    <h2>Contactez-nous</h2>
    
                    <?php if(!empty($msg_contact_etat)) echo $msg_contact_etat; ?>

                    <div class="card">
                     <form method="post" action="index.php?page=contact">
            
            <?= csrf_field() ?>
            
                     <label>Votre Nom :</label>
                    <input type="text" name="cnom" placeholder="Votre nom complet" required>
            
                    <label>Votre Email :</label>
                    <input type="email" name="cemail" placeholder="exemple@email.com" required>
            
                    <label>Votre Message :</label>
                    <textarea name="cmsg" rows="6" placeholder="Écrivez votre message ici..." required></textarea>
            
                    <button type="submit" name="btn_contact">Envoyer le message</button>
                 </form>
                </div>
            <?php break; ?>

                <?php case 'monuments': ?>
    <h2>Sites et Monuments</h2>
    
    <div class="news-item">
        <a href="img/colisee.jpg" target="_blank" title="Voir l'image en grand">
            <img src="img/colisee.jpg" class="float-img" alt="Le Colisée">
        </a>
        <h4>Le Colisée</h4>
        <p>Un amphithéâtre elliptique situé dans le centre de la ville de Rome.</p>
    </div>

    <div class="news-item">
        <a href="img/pise.jpg" target="_blank" title="Voir l'image en grand">
            <img src="img/pise.jpg" class="float-img" alt="Tour de Pise">
        </a>
        <h4>Tour de Pise</h4>
        <p>Le campanile de la cathédrale Notre-Dame de l’Assomption de Pise.</p>
    </div>
<?php break; ?>


                <?php case 'villes': ?>
    <h2>Index des villes</h2>
    <table>
        <tr><th>Nom</th><th>Superficie</th><th>Population</th></tr>
        <tr><td>Rome</td><td>1 285 km²</td><td>2 873 000</td></tr>
        <tr><td>Milan</td><td>181 km²</td><td>1 352 000</td></tr>
        <tr><td>Naples</td><td>117 km²</td><td>967 000</td></tr>
    </table>
    
    <h3>Galerie</h3>
    <a href="img/rome_mini.jpg" target="_blank">
        <img src="img/rome_mini.jpg" width="100" alt="Rome">
    </a>
    <a href="img/venise_mini.jpg" target="_blank">
        <img src="img/venise_mini.jpg" width="100" alt="Venise">
    </a>
<?php break; ?>

                <?php case 'map': ?>
                    <h2>Carte de l'Italie</h2>
                    <div class="map-wrapper">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d12090886.993685257!2d7.662235562723654!3d42.13962649060641!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x12d4fe82448dd203%3A0xe22cf55c24635e6f!2sItaly!5e0!3m2!1sen!2sma!4v1700000000000" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                <?php break; ?>

                <?php case 'liens': ?>
                    <h2>Liens utiles</h2>
                    <ul><li><img src="img/logo1.png" width="20"> <a href="#">Ambassade</a></li><li><img src="img/logo2.png" width="20"> <a href="#">Office du tourisme</a></li></ul>
                <?php break; ?>

                <?php case 'news_detail': 
                    $id = (int)($_GET['id'] ?? 0);
                    $stmt = $pdo->prepare("SELECT * FROM News WHERE news_id = ? LIMIT 1");
                    $stmt->execute([$id]);
                    $news = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($news): ?>
                        <h2><?= e($news['titre']) ?></h2>
                        <small><?= e($news['date_publication']) ?></small>
                        <p><strong><?= e($news['resume']) ?></strong></p>
                        <div class="news-content"><?= nl2br(e($news['contenu'])) ?></div>
                        <p><a href="index.php">Retour accueil</a></p>
                    <?php else: ?>
                        <p>Article introuvable.</p>
                    <?php endif; ?>
                <?php break; ?>

                <?php case 'toutes_news': 
                    $parPage = 10;
                    $pageActuelle = max(1, (int)($_GET['p'] ?? 1));
                    $offset = ($pageActuelle - 1) * $parPage;
                    $total = (int)$pdo->query("SELECT COUNT(*) FROM News")->fetchColumn();
                    $nbPages = max(1, (int)ceil($total / $parPage));

                    $stmt = $pdo->prepare("SELECT * FROM News ORDER BY date_publication DESC LIMIT :lim OFFSET :off");
                    $stmt->bindValue(':lim', $parPage, PDO::PARAM_INT);
                    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
                    $stmt->execute();
                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <div class="news-item">
                            <h4><?= e($row['titre']) ?></h4>
                            <p><?= e($row['resume']) ?></p>
                            <a class="btn-detail" href="index.php?page=news_detail&id=<?= (int)$row['news_id'] ?>">Détails</a>
                        </div>
                    <?php endwhile; ?>
                    <div class="pagination">Page :
                        <?php for($i=1;$i<=$nbPages;$i++): ?>
                            <a href="index.php?page=toutes_news&p=<?= $i ?>"<?php if($i== $pageActuelle) echo ' class="active"'; ?>>[<?= $i ?>]</a>
                        <?php endfor; ?>
                    </div>
                <?php break; ?>

            <?php endswitch; ?>
        </section>

        <aside class="col-droite">
        <section class="video-widget">
            <h4>Documentaire</h4>
            <video width="300" height="300" controls poster="img/poster.jpg">
                    <source src="img/video.mp4" type="video/mp4">
                    <source src="img/video.webm" type="video/webm">

                     Votre navigateur est trop ancien pour lire cette vidéo. 
                    <a href="img/video.mp4">Télécharger la vidéo</a>.
            </video>
        </section>
            <div style="text-align:center; font-size:0.8em;">Poster de la vidéo</div>

            <div class="newsletter-box">
                <h4>S'inscrire à la newsletter</h4>
                <?php if($msg_news) echo '<p class="msg-news">'.e($msg_news).'</p>'; ?>
                <form method="post" action="index.php">
                    <?= csrf_field() ?>
                    <input type="text" name="nom" placeholder="Nom" required>
                    <input type="text" name="prenom" placeholder="Prénom" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <button type="submit" name="btn_newsletter">S'inscrire</button>
                </form>
            </div>

            <div style="margin-top:20px; text-align:center;">
                <a href="admin.php" class="admin-link">Se connecter (Admin)</a>
            </div>
        </aside>
    </div>

    <footer>
        <nav><a href="#">Suggestions</a> | <a href="#">Condition d’utilisation</a></nav>
        <p>Copyright USMBA 2025 - 2026</p>
        <p>Faculté des Sciences et Techniques de Fès</p>
    </footer>

</div>
<script src="assets/script.js"></script>
</body>
</html>
