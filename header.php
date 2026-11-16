<?php
$titre = $titre ?? 'Gestion des étudiants';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($titre) ?> | Gestion des étudiants</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php if (connecte()): ?>
<header class="barre-haute">
    <div class="entete">
        <nav class="navigation" aria-label="Navigation principale">
            <a href="liste.php">Étudiants</a>
            <a href="ajouter.php">Ajouter</a>
            <span class="utilisateur"><?= h((string) $_SESSION['utilisateur']) ?></span>
            <a class="lien-sortie" href="logout.php">Déconnexion</a>
        </nav>
    </div>
</header>
<?php endif; ?>
<main class="contenu">