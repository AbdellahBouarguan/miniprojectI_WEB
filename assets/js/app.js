document.addEventListener('DOMContentLoaded', () => {
    // Afficher / masquer le mot de passe
    document.querySelectorAll('[data-password-toggle]').forEach((bouton) => {
        bouton.addEventListener('click', () => {
            const selecteur = bouton.getAttribute('data-password-toggle');
            const champ = document.querySelector(selecteur);

            if (!champ) {
                return;
            }

            const estCache = champ.getAttribute('type') === 'password';
            champ.setAttribute('type', estCache ? 'text' : 'password');
            bouton.setAttribute('aria-label', estCache ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
        });
    });

    // Aperçu des photos de compteur
    document.querySelectorAll('[data-photo-input]').forEach((input) => {
        input.addEventListener('change', () => {
            const cible = document.querySelector(input.getAttribute('data-preview-target'));
            const fichier = input.files && input.files[0];

            if (!cible || !fichier) {
                return;
            }

            cible.src = URL.createObjectURL(fichier);
            cible.classList.add('show');
        });
    });

    // Confirmation avant suppression
    document.querySelectorAll('[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.getAttribute('data-confirm') || 'Confirmer cette action ?';

            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
});
