<?php
require_once __DIR__ . '/functions.php';
exiger_connexion();

$etudiants = lire_etudiants();
$idBrut = $_GET['id'] ?? null;
$idEdition = is_scalar($idBrut) && $idBrut !== '' ? (int) $idBrut : null;
$modeEdition = $idEdition !== null && $idEdition > 0;
$etudiantExistant = $modeEdition ? ($etudiants[$idEdition] ?? null) : null;

if ($modeEdition && $etudiantExistant === null) {
    $_SESSION['flash'] = 'Étudiant introuvable.';
    header('Location: liste.php');
    exit;
}

$titre = $modeEdition ? 'Modifier un étudiant' : 'Ajouter un étudiant';
$erreurs = [];
$valeurs = [
    'nom' => $etudiantExistant['nom'] ?? '',
    'prenom' => $etudiantExistant['prenom'] ?? '',
    'filiere' => $etudiantExistant['filiere'] ?? '',
    'note' => $etudiantExistant['note'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['nom', 'prenom', 'filiere', 'note'] as $champ) {
        $valeur = $_POST[$champ] ?? '';
        $valeurs[$champ] = is_string($valeur) ? nettoyer($valeur) : '';
    }

    if ($valeurs['nom'] === '') {
        $erreurs[] = 'Le nom est obligatoire.';
    }
    if ($valeurs['prenom'] === '') {
        $erreurs[] = 'Le prénom est obligatoire.';
    }
    if (!in_array($valeurs['filiere'], FILIERES, true)) {
        $erreurs[] = 'Choisissez une filière valide.';
    }
    if (!is_numeric($valeurs['note']) || (float) $valeurs['note'] < 0 || (float) $valeurs['note'] > 20) {
        $erreurs[] = 'La note doit être un nombre entre 0 et 20.';
    }

    $photoActuelle = $etudiantExistant['photo'] ?? '';
    $photoEnvoyee = false;
    $fichierPhoto = $_FILES['photo'] ?? null;

    if (is_array($fichierPhoto) && isset($fichierPhoto['error']) && (int) $fichierPhoto['error'] !== UPLOAD_ERR_NO_FILE) {
        $codeErreur = (int) $fichierPhoto['error'];
        if ($codeErreur !== UPLOAD_ERR_OK) {
            $erreurs[] = 'La photo n’a pas pu être envoyée.';
        } elseif ((int) ($fichierPhoto['size'] ?? 0) > 1024 * 1024) {
            $erreurs[] = 'La photo ne doit pas dépasser 1 Mo.';
        } else {
            $extension = strtolower(pathinfo((string) ($fichierPhoto['name'] ?? ''), PATHINFO_EXTENSION));
            if (!in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
                $erreurs[] = 'La photo doit être au format JPG, JPEG ou PNG.';
            } else {
                $photoEnvoyee = true;
            }
        }
    }

    if ($erreurs === []) {
        $photo = $photoActuelle;

        if ($photoEnvoyee && is_array($fichierPhoto)) {
            $extension = strtolower(pathinfo((string) $fichierPhoto['name'], PATHINFO_EXTENSION));
            $nomPhoto = uniqid('etu_', true) . '.' . $extension;
            $destination = __DIR__ . '/uploads/' . $nomPhoto;

            if (!move_uploaded_file((string) $fichierPhoto['tmp_name'], $destination)) {
                $erreurs[] = 'Impossible d’enregistrer la photo envoyée.';
            } else {
                $photo = $nomPhoto;
            }
        }

        if ($erreurs === []) {
            if ($modeEdition && $etudiantExistant !== null) {
                $etudiants[$idEdition] = [
                    'id' => $idEdition,
                    'nom' => $valeurs['nom'],
                    'prenom' => $valeurs['prenom'],
                    'filiere' => $valeurs['filiere'],
                    'note' => $valeurs['note'],
                    'photo' => $photo,
                ];
                if ($photoEnvoyee && $photoActuelle !== '') {
                    supprimer_photo($photoActuelle);
                }
                $message = 'Les informations de l’étudiant ont été mises à jour.';
            } else {
                $ids = array_keys($etudiants);
                $nouvelId = $ids === [] ? 1 : max(array_map('intval', $ids)) + 1;
                $etudiants[$nouvelId] = [
                    'id' => $nouvelId,
                    'nom' => $valeurs['nom'],
                    'prenom' => $valeurs['prenom'],
                    'filiere' => $valeurs['filiere'],
                    'note' => $valeurs['note'],
                    'photo' => $photo,
                ];
                $message = 'L’étudiant a été ajouté.';
            }

            sauver_etudiants($etudiants);
            $_SESSION['flash'] = $message;
            header('Location: liste.php');
            exit;
        }
    }
}

include __DIR__ . '/header.php';
?>
<section class="formulaire-page">
    <p class="surtitre"><?= $modeEdition ? 'Fiche étudiant' : 'Nouvel étudiant' ?></p>
    <h1><?= $modeEdition ? 'Modifier un étudiant' : 'Ajouter un étudiant' ?></h1>
    <p class="texte-secondaire">Les champs marqués d’un astérisque sont obligatoires.</p>

    <?php if ($erreurs !== []): ?>
        <div class="message erreur" role="alert">
            <strong>Vérifiez les informations :</strong>
            <ul>
                <?php foreach ($erreurs as $erreur): ?><li><?= h($erreur) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form class="carte formulaire" method="post" enctype="multipart/form-data">
        <div class="grille-formulaire">
            <div class="champ">
                <label for="nom">Nom *</label>
                <input id="nom" name="nom" type="text" value="<?= h($valeurs['nom']) ?>" required>
            </div>
            <div class="champ">
                <label for="prenom">Prénom *</label>
                <input id="prenom" name="prenom" type="text" value="<?= h($valeurs['prenom']) ?>" required>
            </div>
            <div class="champ">
                <label for="filiere">Filière *</label>
                <select id="filiere" name="filiere" required>
                    <option value="">Choisir une filière</option>
                    <?php foreach (FILIERES as $filiere): ?>
                        <option value="<?= h($filiere) ?>" <?= $valeurs['filiere'] === $filiere ? 'selected' : '' ?>><?= h($filiere) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="champ">
                <label for="note">Note sur 20 *</label>
                <input id="note" name="note" type="number" min="0" max="20" step="any" value="<?= h($valeurs['note']) ?>" required>
            </div>
            <div class="champ champ-large">
                <label for="photo">Photo de profil <span class="texte-secondaire">(facultative, JPG/PNG, 1 Mo max.)</span></label>
                <input id="photo" name="photo" type="file" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                <?php if ($modeEdition && !empty($etudiantExistant['photo']) && is_file(__DIR__ . '/uploads/' . basename($etudiantExistant['photo']))): ?>
                    <p class="apercu-photo">Photo actuelle : <img class="photo-profil" src="uploads/<?= h(basename($etudiantExistant['photo'])) ?>" alt="Photo actuelle"></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="actions-formulaire">
            <button class="bouton" type="submit"><?= $modeEdition ? 'Enregistrer les changements' : 'Ajouter l’étudiant' ?></button>
            <a class="bouton bouton-secondaire" href="liste.php">Annuler</a>
        </div>
    </form>
</section>
<?php include __DIR__ . '/footer.php'; ?>