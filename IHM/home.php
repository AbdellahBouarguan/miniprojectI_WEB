<?php
require ROOT . 'IHM/layout/header.php';

$role = $_SESSION['role'] ?? null;
$prenom = trim((string) ($_SESSION['prenom'] ?? ''));

$derniereConso = null;
$nbFactures = 0;
$nbClients = 0;
$nbAnomalies = 0;
$nbFacturesFournisseur = 0;

if ($role === 'client') {
    $idClient = (int) ($_SESSION['id_client'] ?? 0);

    $stmt = mysqli_prepare(
        db(),
        'SELECT periode_mois, periode_annee, statut, index_eau, index_elec
         FROM consommation
         WHERE id_client = ?
         ORDER BY periode_annee DESC, periode_mois DESC
         LIMIT 1'
    );
    mysqli_stmt_bind_param($stmt, 'i', $idClient);
    mysqli_stmt_execute($stmt);
    $resultat = mysqli_stmt_get_result($stmt);
    $derniereConso = mysqli_fetch_assoc($resultat) ?: null;

    $stmt = mysqli_prepare(db(), 'SELECT COUNT(*) FROM facture WHERE id_client = ?');
    mysqli_stmt_bind_param($stmt, 'i', $idClient);
    mysqli_stmt_execute($stmt);
    $resultat = mysqli_stmt_get_result($stmt);
    $ligne = mysqli_fetch_row($resultat);
    $nbFactures = (int) ($ligne[0] ?? 0);
} elseif ($role === 'fournisseur') {
    $idFournisseur = (int) ($_SESSION['id_fournisseur'] ?? 0);

    $stmt = mysqli_prepare(db(), 'SELECT COUNT(*) FROM client WHERE id_fournisseur = ?');
    mysqli_stmt_bind_param($stmt, 'i', $idFournisseur);
    mysqli_stmt_execute($stmt);
    $resultat = mysqli_stmt_get_result($stmt);
    $ligne = mysqli_fetch_row($resultat);
    $nbClients = (int) ($ligne[0] ?? 0);

    $stmt = mysqli_prepare(
        db(),
        "SELECT COUNT(*) FROM anomalie
         WHERE id_fournisseur = ? AND statut = 'EN_ATTENTE'"
    );
    mysqli_stmt_bind_param($stmt, 'i', $idFournisseur);
    mysqli_stmt_execute($stmt);
    $resultat = mysqli_stmt_get_result($stmt);
    $ligne = mysqli_fetch_row($resultat);
    $nbAnomalies = (int) ($ligne[0] ?? 0);

    $stmt = mysqli_prepare(
        db(),
        'SELECT COUNT(*)
         FROM facture f
         JOIN client c ON c.id_client = f.id_client
         WHERE c.id_fournisseur = ?'
    );
    mysqli_stmt_bind_param($stmt, 'i', $idFournisseur);
    mysqli_stmt_execute($stmt);
    $resultat = mysqli_stmt_get_result($stmt);
    $ligne = mysqli_fetch_row($resultat);
    $nbFacturesFournisseur = (int) ($ligne[0] ?? 0);
}

$aujourdhui = new DateTimeImmutable('today');
$periodeOuverte = (int) $aujourdhui->format('j') >= 18;
$finMois = $aujourdhui->modify('last day of this month');
$joursRestants = max(0, (int) $aujourdhui->diff($finMois)->days);
?>

<?php if ($role === null): ?>
    <section class="hero">
        <p class="eyebrow" style="text-align: left; color: #9cc9ff;">AQUAVOLT</p>
        <h1>Vos factures d’eau et d’électricité, simplement.</h1>
        <p>
            Suivez vos consommations, saisissez vos index et consultez vos factures
            depuis un seul espace sécurisé.
        </p>
        <p style="margin-top: 1.4rem;">
            <a class="btn" href="<?= e(url('Auth/login')) ?>" style="background: #fff; color: #0b2a4a;">
                Se connecter
            </a>
        </p>
    </section>

    <div class="grid">
        <div class="card">
            <h2>Suivi des consommations</h2>
            <p class="muted">Saisissez vos index d’eau et d’électricité chaque mois.</p>
        </div>
        <div class="card">
            <h2>Factures claires</h2>
            <p class="muted">Consultez le détail de vos factures et vos montants.</p>
        </div>
        <div class="card">
            <h2>Anomalies détectées</h2>
            <p class="muted">Un contrôle automatique vérifie la cohérence de vos relevés.</p>
        </div>
    </div>
<?php elseif ($role === 'client'): ?>
    <div class="page-head">
        <div>
            <h1>Bonjour, <?= e($prenom !== '' ? $prenom : 'Client') ?></h1>
            <p>Bienvenue dans votre espace AquaVolt.</p>
        </div>
    </div>

    <div class="stats">
        <div class="stat-card">
            <div class="stat-label">Dernière consommation</div>
            <div class="stat-value" style="font-size: 1.3rem;">
                <?php if ($derniereConso !== null): ?>
                    <?= (int) $derniereConso['periode_mois'] ?>/<?= (int) $derniereConso['periode_annee'] ?>
                <?php else: ?>
                    Aucune
                <?php endif; ?>
            </div>
            <div class="stat-note">
                <?= $derniereConso !== null ? e($derniereConso['statut']) : 'Aucun relevé enregistré' ?>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Mes factures</div>
            <div class="stat-value"><?= $nbFactures ?></div>
            <div class="stat-note">Facture<?= $nbFactures > 1 ? 's' : '' ?> disponible<?= $nbFactures > 1 ? 's' : '' ?></div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Période de saisie</div>
            <div class="stat-value" style="font-size: 1.3rem;">
                <?= $periodeOuverte ? 'Ouverte' : 'Fermée' ?>
            </div>
            <div class="stat-note">
                <?= $periodeOuverte
                    ? 'Il vous reste ' . $joursRestants . ' jour' . ($joursRestants > 1 ? 's' : '')
                    : 'Ouverture le 18 du mois' ?>
            </div>
        </div>
    </div>

    <div class="grid">
        <a class="card link-card" href="<?= e(url('Consommation/add')) ?>">
            <h2>Saisir ma consommation</h2>
            <p class="muted">Transmettez vos index et les photos de vos compteurs.</p>
        </a>
        <a class="card link-card" href="<?= e(url('Facture/all')) ?>">
            <h2>Mes factures</h2>
            <p class="muted">Consultez vos factures et leur état de paiement.</p>
        </a>
    </div>
<?php else: ?>
    <div class="page-head">
        <div>
            <h1>Tableau de bord</h1>
            <p>Suivi de votre régie eau et électricité.</p>
        </div>
    </div>

    <div class="stats">
        <div class="stat-card">
            <div class="stat-label">Clients</div>
            <div class="stat-value"><?= $nbClients ?></div>
            <div class="stat-note">Abonnés enregistrés</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Anomalies en attente</div>
            <div class="stat-value"><?= $nbAnomalies ?></div>
            <div class="stat-note">Relevés à vérifier</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Factures</div>
            <div class="stat-value"><?= $nbFacturesFournisseur ?></div>
            <div class="stat-note">Factures émises</div>
        </div>
    </div>

    <div class="grid">
        <a class="card link-card" href="<?= e(url('Client/all')) ?>">
            <h2>Gestion des clients</h2>
            <p class="muted">Ajoutez, modifiez ou consultez les abonnés.</p>
        </a>
        <a class="card link-card" href="<?= e(url('Anomalie/index')) ?>">
            <h2>Anomalies de saisie</h2>
            <p class="muted">Corrigez les relevés signalés avant facturation.</p>
        </a>
        <a class="card link-card" href="<?= e(url('Facture/all')) ?>">
            <h2>Factures</h2>
            <p class="muted">Consultez les factures de vos clients.</p>
        </a>
    </div>
<?php endif; ?>

<?php require ROOT . 'IHM/layout/footer.php'; ?>
