<?php
declare(strict_types=1);

const FICHIER = __DIR__ . '/data/etudiants.txt';
const FILIERES = ['GI', 'IDIA', 'GE', 'GC'];
const UTILISATEURS = [
    'admin' => 'admin123',
];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function connecte(): bool
{
    return isset($_SESSION['utilisateur']) && is_string($_SESSION['utilisateur']);
}

function exiger_connexion(): void
{
    if (!connecte()) {
        header('Location: index.php');
        exit;
    }
}

function nettoyer(string $s): string
{
    $s = str_replace([';', "\r", "\n"], '', $s);
    $s = trim($s);
    return preg_replace('/[ \t]+/u', ' ', $s) ?? $s;
}

function lire_etudiants(): array
{
    if (!is_file(FICHIER) || !is_readable(FICHIER)) {
        return [];
    }

    $lignes = file(FICHIER, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lignes === false) {
        return [];
    }

    $liste = [];
    foreach ($lignes as $ligne) {
        $champs = explode(';', trim($ligne));
        if (count($champs) !== 6 || !ctype_digit($champs[0])) {
            continue;
        }

        $id = (int) $champs[0];
        $liste[$id] = [
            'id' => $id,
            'nom' => $champs[1],
            'prenom' => $champs[2],
            'filiere' => $champs[3],
            'note' => $champs[4],
            'photo' => $champs[5],
        ];
    }

    return $liste;
}

function sauver_etudiants(array $liste): void
{
    $lignes = [];

    foreach ($liste as $cle => $etudiant) {
        if (!is_array($etudiant)) {
            continue;
        }

        $id = (string) ($etudiant['id'] ?? $cle);
        if (!ctype_digit($id)) {
            continue;
        }

        $champs = [
            $id,
            nettoyer((string) ($etudiant['nom'] ?? '')),
            nettoyer((string) ($etudiant['prenom'] ?? '')),
            nettoyer((string) ($etudiant['filiere'] ?? '')),
            nettoyer((string) ($etudiant['note'] ?? '')),
            nettoyer((string) ($etudiant['photo'] ?? '')),
        ];
        $lignes[] = implode(';', $champs);
    }

    $contenu = $lignes === [] ? '' : implode(PHP_EOL, $lignes) . PHP_EOL;
    if (file_put_contents(FICHIER, $contenu, LOCK_EX) === false) {
        throw new RuntimeException('Impossible d’écrire dans le fichier des étudiants.');
    }
}

function supprimer_photo(string $nomPhoto): void
{
    $nomPhoto = basename($nomPhoto);
    if ($nomPhoto === '') {
        return;
    }

    $chemin = __DIR__ . '/uploads/' . $nomPhoto;
    if (is_file($chemin)) {
        unlink($chemin);
    }
}