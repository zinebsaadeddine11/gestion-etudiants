<?php
require_once __DIR__ . '/functions.php';
exiger_connexion();

$idBrut = $_GET['id'] ?? null;
$id = is_scalar($idBrut) ? (int) $idBrut : 0;
$etudiants = lire_etudiants();

if ($id > 0 && isset($etudiants[$id])) {
    $photo = $etudiants[$id]['photo'];
    unset($etudiants[$id]);
    sauver_etudiants($etudiants);
    if ($photo !== '') {
        supprimer_photo($photo);
    }
    $_SESSION['flash'] = 'L’étudiant a été supprimé.';
} else {
    $_SESSION['flash'] = 'Étudiant introuvable.';
}

header('Location: liste.php');
exit;