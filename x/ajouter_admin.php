<?php
// ajouter_admin.php
require 'db.php'; // Votre fichier de connexion à la base ($pdo)

$message = "";

if (isset($_POST['btn_ajouter'])) {
    $login = trim($_POST['login']);
    $pass_clair = $_POST['password'];

    if (!empty($login) && !empty($pass_clair)) {
        // 1. C'est ICI que la magie opère : PHP hache le mot de passe automatiquement
        $pass_hache = password_hash($pass_clair, PASSWORD_DEFAULT);

        // 2. On insère le login et le mot de passe HACHÉ dans la base
        $stmt = $pdo->prepare("INSERT INTO Admin (login, password) VALUES (?, ?)");
        
        if($stmt->execute([$login, $pass_hache])){
            $message = "Succès ! L'admin '$login' a été ajouté avec un mot de passe sécurisé.";
        } else {
            $message = "Erreur lors de l'ajout.";
        }
    } else {
        $message = "Veuillez remplir tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Ajouter Admin</title></head>
<body>
    <h1>Créer un nouvel administrateur</h1>
    
    <?php if($message): ?>
        <p style="color:green; font-weight:bold;"><?= $message ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Nouveau Login :</label><br>
        <input type="text" name="login" required><br><br>

        <label>Nouveau Mot de passe :</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit" name="btn_ajouter">Enregistrer l'admin</button>
    </form>
</body>
</html>