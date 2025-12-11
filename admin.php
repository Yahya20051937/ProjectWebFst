<?php
// admin.php - Espace administration (sécurisé)
// Keep structure similar but improve auth flow, CSRF, action via POST for destructive ops

session_start();
require __DIR__ . '/db.php';

function e($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

// CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
}

$err = "";
// Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Login handling
if (isset($_POST['login_submit'])) {
    $login = $_POST['login'] ?? '';
    $pass = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM Admin WHERE login = ? LIMIT 1");
    $stmt->execute([$login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && ($pass === $user['password'] || password_verify($pass, $user['password']))){
        session_regenerate_id(true);
        $_SESSION['admin_logged'] = true;
        $_SESSION['admin_login'] = $user['login'];
        header('Location: admin.php');
        exit;
    } else {

        $err = "Identifiants incorrects";
    }
}

$is_admin = isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true;

// Handle news save (add/edit)
$edit_mode = false;
$titre = $resume = $contenu = "";
$id_to_edit = 0;

// Delete via POST
if ($is_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['del_news'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $err = "Requête invalide (token).";
    } else {
        $id = (int)$_POST['del_news'];
        $pdo->prepare("DELETE FROM News WHERE news_id = ?")->execute([$id]);
        header('Location: admin.php');
        exit;
    }
}

// Edit request (GET)
if ($is_admin && isset($_GET['edit'])) {
    $id_to_edit = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM News WHERE news_id = ? LIMIT 1");
    $stmt->execute([$id_to_edit]);
    $news = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($news) {
        $edit_mode = true;
        $titre = $news['titre'];
        $resume = $news['resume'];
        $contenu = $news['contenu'];
    }
}

// Save news (POST)
if ($is_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_news'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $err = "Requête invalide (token).";
    } else {
        $t = trim($_POST['titre'] ?? '');
        $r = trim($_POST['resume'] ?? '');
        $c = trim($_POST['contenu'] ?? '');
        $id = (int)($_POST['news_id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE News SET titre = ?, resume = ?, contenu = ? WHERE news_id = ?");
            $stmt->execute([$t, $r, $c, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO News (titre, resume, contenu, date_publication) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$t, $r, $c]);
        }
        header('Location: admin.php');
        exit;
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Administration</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="site-container" style="padding:20px;">
    <h1>Espace Administration</h1>
    <a href="index.php">Retour au site</a>

    <?php if (!$is_admin): ?>
        <div class="login-panel">
            <h3>Se connecter</h3>
            <?php if($err): ?><p class="error"><?= e($err) ?></p><?php endif; ?>
            <form method="post">
                <label>Login: <input type="text" name="login" required></label><br><br>
                <label>Pass: <input type="password" name="password" required></label><br><br>
                <button type="submit" name="login_submit">Connexion</button>
            </form>
        </div>
    <?php else: ?>
        <p>Bienvenue <?= e($_SESSION['admin_login'] ?? 'Admin') ?> | <a href="admin.php?action=logout">Déconnexion</a></p>
        <hr>

        <div class="card">
            <h3><?= $edit_mode ? 'Modifier la news' : 'Ajouter une news' ?></h3>
            <?php if($err): ?><p class="error"><?= e($err) ?></p><?php endif; ?>
            <form method="post">
                <input type="hidden" name="news_id" value="<?= (int)$id_to_edit ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                <label>Titre : <input type="text" name="titre" value="<?= e($titre) ?>" required style="width:100%"></label><br>
                <label>Résumé : <textarea name="resume" rows="3" required style="width:100%"><?= e($resume) ?></textarea></label><br>
                <label>Contenu : <textarea name="contenu" rows="10" required style="width:100%"><?= e($contenu) ?></textarea></label><br>
                <button type="submit" name="save_news"><?= $edit_mode ? 'Mettre à jour' : 'Publier' ?></button>
                <?php if($edit_mode): ?> <a href="admin.php">Annuler</a> <?php endif; ?>
            </form>
        </div>

        <h3>Liste des News</h3>
        <table class="admin-table">
            <tr><th>Date</th><th>Titre</th><th>Actions</th></tr>
            <?php
            $stmt = $pdo->query("SELECT * FROM News ORDER BY date_publication DESC");
            while($r = $stmt->fetch(PDO::FETCH_ASSOC)):
            ?>
                <tr>
                    <td><?= e($r['date_publication']) ?></td>
                    <td><?= e($r['titre']) ?></td>
                    <td>
                        <a href="admin.php?edit=<?= (int)$r['news_id'] ?>">Modifier</a>
                        <form method="post" style="display:inline" onsubmit="return confirm('Supprimer ?')">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" name="del_news" value="<?= (int)$r['news_id'] ?>" class="link-like">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <h3>Inscrits Newsletter</h3>
        <ul>
        <?php
        $stmt = $pdo->query("SELECT * FROM Internaute ORDER BY id DESC");
        while($u = $stmt->fetch(PDO::FETCH_ASSOC)):
            echo '<li>'.e($u['nom']).' '.e($u['prenom']).' ('.e($u['email']).')</li>';
        endwhile;
        ?>
        </ul>

    <?php endif; ?>
</div>
</body>
</html>
