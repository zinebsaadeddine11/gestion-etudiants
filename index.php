<?php
require_once __DIR__ . '/functions.php';

if (connecte()) {
    header('Location: liste.php');
    exit;
}

$titre = 'Connexion';
$erreur = '';
$cookieLogin = $_COOKIE['dernier_user'] ?? '';
$login = is_string($cookieLogin) ? $cookieLogin : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valeurLogin = $_POST['login'] ?? '';
    $valeurMotDePasse = $_POST['mdp'] ?? '';
    $login = is_string($valeurLogin) ? trim($valeurLogin) : '';
    $motDePasse = is_string($valeurMotDePasse) ? $valeurMotDePasse : '';

    if (isset(UTILISATEURS[$login]) && hash_equals(UTILISATEURS[$login], $motDePasse)) {
        session_regenerate_id(true);
        $_SESSION['utilisateur'] = $login;
        setcookie('dernier_user', $login, [
            'expires' => time() + 7 * 24 * 60 * 60,
            'path' => '/gestion_etudiants/',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        header('Location: liste.php');
        exit;
    }

    $erreur = 'Identifiant ou mot de passe incorrect.';
}

include __DIR__ . '/header.php';
?>
<section class="auth-layout">
    <section class="carte connexion">
        <p class="surtitre">Votre espace</p>
        <h1>Content de vous revoir</h1>
        <p class="texte-secondaire">Connectez-vous pour continuer.</p>

        <?php if ($erreur !== ''): ?>
            <p class="message erreur" role="alert"><?= h($erreur) ?></p>
        <?php endif; ?>

        <form method="post" action="index.php">
            <div class="champ">
                <label for="login">Login</label>
                <input type="text" id="login" name="login" value="<?= h($login) ?>" autocomplete="username" required autofocus>
            </div>
            <div class="champ">
                <label for="mdp">Mot de passe</label>
                <input type="password" id="mdp" name="mdp" autocomplete="current-password" required>
            </div>
            <button class="bouton" type="submit">Se connecter </button>
        </form>
       
    </section>
</section>
<?php include __DIR__ . '/footer.php'; ?>