document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('a.suppression').forEach((lien) => {
        lien.addEventListener('click', (evenement) => {
            const message = lien.dataset.confirm || 'Confirmer la suppression ?';
            if (!window.confirm(message)) {
                evenement.preventDefault();
            }
        });
    });

    const recherche = document.querySelector('#recherche');
    const lignes = Array.from(document.querySelectorAll('tr.ligne-etudiant'));
    const ligneVide = document.querySelector('tr.ligne-vide');

    if (recherche && lignes.length > 0) {
        const filtrer = () => {
            const terme = recherche.value.trim().toLocaleLowerCase();
            let visibles = 0;

            lignes.forEach((ligne) => {
                const texte = (ligne.dataset.recherche || ligne.textContent).toLocaleLowerCase();
                const correspond = texte.includes(terme);
                ligne.hidden = !correspond;
                if (correspond) visibles += 1;
            });

            if (ligneVide) {
                ligneVide.hidden = visibles !== 0;
            }
        };

        recherche.addEventListener('input', filtrer);
        filtrer();
    }
});