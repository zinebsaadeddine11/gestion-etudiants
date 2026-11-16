<?php
require_once __DIR__ . '/functions.php';
exiger_connexion();

$titre = 'Étudiants';
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$etudiants = array_values(lire_etudiants());
$q = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$tri = isset($_GET['tri']) && is_string($_GET['tri']) ? $_GET['tri'] : 'ajout';
$trisAutorises = ['ajout', 'nom', 'filiere', 'note'];
if (!in_array($tri, $trisAutorises, true)) {
    $tri = 'ajout';
}

if ($q !== '') {
    $etudiants = array_values(array_filter($etudiants, static function (array $etudiant) use ($q): bool {
        return stripos($etudiant['nom'] . ' ' . $etudiant['prenom'], $q) !== false;
    }));
}

if ($tri === 'nom') {
    usort($etudiants, static fn(array $a, array $b): int => strcasecmp($a['nom'], $b['nom']));
} elseif ($tri === 'filiere') {
    usort($etudiants, static fn(array $a, array $b): int => strcasecmp($a['filiere'], $b['filiere']));
} elseif ($tri === 'note') {
    usort($etudiants, static fn(array $a, array $b): int => (float) $b['note'] <=> (float) $a['note']);
}

$notes = array_map(static fn(array $etudiant): float => (float) $etudiant['note'], $etudiants);
$nombreAffiche = count($etudiants);
$moyenne = $nombreAffiche > 0 ? array_sum($notes) / $nombreAffiche : null;
$meilleureNote = $nombreAffiche > 0 ? max($notes) : null;

include __DIR__ . '/header.php';
?>
<section class="page-entete">
    <div>
        <p class="surtitre">Répertoire</p>
        <h1>Étudiants</h1>
        <p class="texte-secondaire">Consultez, recherchez et gérez les étudiants enregistrés.</p>
    </div>
    <a class="bouton" href="ajouter.php">＋ Ajouter un étudiant</a>
</section>

<?php if (is_string($flash) && $flash !== ''): ?>
    <p class="message succes" role="status"><?= h($flash) ?></p>
<?php endif; ?>

<section class="statistiques" aria-label="Statistiques">
    <article class="stat-carte"><span>Affichés</span><strong><?= $nombreAffiche ?></strong></article>
    <article class="stat-carte"><span>Moyenne</span><strong><?= $moyenne === null ? '—' : number_format($moyenne, 2, ',', ' ') ?></strong></article>
    <article class="stat-carte"><span>Meilleure note</span><strong><?= $meilleureNote === null ? '—' : number_format($meilleureNote, 2, ',', ' ') ?></strong></article>
</section>

<section class="panneau">
    <form class="filtres" method="get" action="liste.php">
        <div class="champ recherche-champ">
            <label for="recherche">Recherche</label>
            <input id="recherche" type="search" name="q" value="<?= h($q) ?>" placeholder="Nom ou prénom…" autocomplete="off">
        </div>
        <div class="champ">
            <label for="tri">Trier par</label>
            <select id="tri" name="tri">
                <option value="ajout" <?= $tri === 'ajout' ? 'selected' : '' ?>>Ordre d’ajout</option>
                <option value="nom" <?= $tri === 'nom' ? 'selected' : '' ?>>Nom</option>
                <option value="filiere" <?= $tri === 'filiere' ? 'selected' : '' ?>>Filière</option>
                <option value="note" <?= $tri === 'note' ? 'selected' : '' ?>>Note décroissante</option>
            </select>
        </div>
        <button class="bouton bouton-secondaire" type="submit">Appliquer</button>
    </form>

    <div class="tableau-defilement">
        <table>
            <thead>
                <tr>
                    <th scope="col">Photo</th>
                    <th scope="col">Nom</th>
                    <th scope="col">Prénom</th>
                    <th scope="col">Filière</th>
                    <th scope="col">Note</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($etudiants as $etudiant): ?>
                <?php
                    $photo = basename($etudiant['photo']);
                    $photoExiste = $photo !== '' && is_file(__DIR__ . '/uploads/' . $photo);
                    $classeNote = (float) $etudiant['note'] >= 10 ? 'note-positive' : 'note-negative';
                    $rechercheLigne = strtolower($etudiant['nom'] . ' ' . $etudiant['prenom'] . ' ' . $etudiant['filiere']);
                ?>
                <tr class="ligne-etudiant" data-recherche="<?= h($rechercheLigne) ?>">
                    <td>
                        <?php if ($photoExiste): ?>
                            <img class="photo-profil" src="uploads/<?= h($photo) ?>" alt="Photo de <?= h($etudiant['prenom']) ?>">
                        <?php else: ?>
                            <span class="avatar" aria-label="Aucune photo"><?= h(strtoupper(substr($etudiant['prenom'], 0, 1))) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="nom-etudiant"><?= h(strtoupper($etudiant['nom'])) ?></td>
                    <td><?= h(ucfirst($etudiant['prenom'])) ?></td>
                    <td><span class="badge-filiere"><?= h($etudiant['filiere']) ?></span></td>
                    <td><span class="note <?= $classeNote ?>"><?= h($etudiant['note']) ?>/20</span></td>
                    <td class="actions">
                        <a class="lien-action" href="ajouter.php?id=<?= (int) $etudiant['id'] ?>">Modifier</a>
                        <a class="lien-action lien-danger suppression" href="supprimer.php?id=<?= (int) $etudiant['id'] ?>" data-confirm="Supprimer cet étudiant ?">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
                <tr class="ligne-vide" <?= $nombreAffiche > 0 ? 'hidden' : '' ?>>
                    <td colspan="6">Aucun étudiant ne correspond à votre recherche.</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>
<?php include __DIR__ . '/footer.php'; ?>