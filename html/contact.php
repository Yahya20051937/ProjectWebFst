<script>window.scrollToEightyPercent && window.scrollToEightyPercent();</script>
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
