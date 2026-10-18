<?php require ROOT . 'IHM/layout/header.php'; ?>

<section class="card">
    <h1>Mini Projet 1 - Version Alpha</h1>
    <p>Application web de gestion des factures d'eau et d'electricite.</p>
    <p>Structure de depart prete : base de donnees, routeur, configuration et dossiers de travail.</p>
</section>

<section class="grid">
    <a class="card link-card" href="<?= url('Auth/login') ?>">
        <h2>Connexion</h2>
        <p>Acceder a l'espace client ou fournisseur.</p>
    </a>
    <a class="card link-card" href="<?= url('Client/all') ?>">
        <h2>Clients</h2>
        <p>Gestion des clients par le fournisseur.</p>
    </a>
    <a class="card link-card" href="<?= url('Consommation/add') ?>">
        <h2>Consommations</h2>
        <p>Saisie des index et des photos des compteurs.</p>
    </a>
    <a class="card link-card" href="<?= url('Facture/all') ?>">
        <h2>Factures</h2>
        <p>Consultation des factures client et fournisseur.</p>
    </a>
    <a class="card link-card" href="<?= url('Anomalie/index') ?>">
        <h2>Anomalies</h2>
        <p>Consultation des anomalies de saisie.</p>
    </a>
</section>

<?php require ROOT . 'IHM/layout/footer.php'; ?>
