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
<link rel="stylesheet" href="css/style.css">
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
            <?php
            switch($page) {
                case 'accueil':
                    include __DIR__ . '/html/accueil.php';
                    break;
                case 'plan':
                    include __DIR__ . '/html/plan.html';
                    break;
                case 'quisommesnous':
                    include __DIR__ . '/html/qui_sommes_nous.html';
                    break;
                case 'contact':
                    include __DIR__ . '/html/contact.php';
                    break;
                case 'monuments':
                    include __DIR__ . '/html/monuments.html';
                    break;
                case 'villes':
                    include __DIR__ . '/html/ville.html';
                    break;
                case 'map':
                    include __DIR__ . '/html/carte.html';
                    break;
                case 'liens':
                    include __DIR__ . '/html/lien_utiles.html';
                    break;
                case 'news_detail':
                    // ...existing code...
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
                    <?php endif;
                    break;
                case 'toutes_news':
                    // ...existing code...
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
                    <?php
                    break;
            }
            ?>
        </section>

        <aside class="col-droite">
        <section class="video-widget">
            <h4>Documentaire</h4>
            <video width="300" height="300" controls poster="assets/img/poster.jpg">
                    <source src="assets/video/video.mp4" type="video/mp4">
                    <source src="assets/img/video.webm" type="video/webm">

                     Votre navigateur est trop ancien pour lire cette vidéo. 
                    <a href="assets/video/video.mp4">Télécharger la vidéo</a>.
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
<script src="assets/js/script.js"></script>
</body>
</html>
