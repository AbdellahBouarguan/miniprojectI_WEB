<?php
require ROOT . 'IHM/layout/header.php';

$role = $_SESSION['role'] ?? null;
$prenom = trim((string) ($_SESSION['prenom'] ?? ''));

$derniereConso = null;
$nbFactures = 0;

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
                <?= $derniereConso !== null ? e(statut_label($derniereConso['statut'])) : 'Aucun relevé enregistré' ?>
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
    <?php
    /** @var array{dashboard: array} $dashboard */
    $totaux = $dashboard['totaux'];
    $globaux = $dashboard['totaux_globaux'];
    $annee = (int) $dashboard['annee'];
    $nbImpayees = (int) $totaux['nb_impayees'];
    $nbPayees = (int) $totaux['nb_payees'];
    $aDesDonnees = (int) $totaux['nb_factures'] > 0;
    ?>
    <div class="page-head">
        <div>
            <h1>Tableau de bord</h1>
            <p>Suivi des impayés et de l’évolution mensuelle des consommations.</p>
        </div>

        <form class="filters" method="get" action="<?= e(url('')) ?>">
            <label for="annee">Année</label>
            <select id="annee" name="annee">
                <?php if ($dashboard['annees'] === []): ?>
                    <option value="<?= $annee ?>"><?= $annee ?></option>
                <?php endif; ?>
                <?php foreach ($dashboard['annees'] as $anneeDisponible): ?>
                    <option value="<?= (int) $anneeDisponible ?>"<?= (int) $anneeDisponible === $annee ? ' selected' : '' ?>>
                        <?= (int) $anneeDisponible ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="button button-small" type="submit">Afficher</button>
        </form>
    </div>

    <div class="stats stats-4">
        <div class="stat-card stat-alert">
            <div class="stat-label">Total impayé <?= $annee ?></div>
            <div class="stat-value"><?= e(format_nombre($totaux['impaye'])) ?> DH</div>
            <div class="stat-note">
                <?= $nbImpayees ?> facture<?= $nbImpayees > 1 ? 's' : '' ?> impayée<?= $nbImpayees > 1 ? 's' : '' ?>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Total encaissé <?= $annee ?></div>
            <div class="stat-value"><?= e(format_nombre($totaux['encaisse'])) ?> DH</div>
            <div class="stat-note">
                <?= $nbPayees ?> facture<?= $nbPayees > 1 ? 's' : '' ?> payée<?= $nbPayees > 1 ? 's' : '' ?>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Clients</div>
            <div class="stat-value"><?= (int) $dashboard['nb_clients'] ?></div>
            <div class="stat-note">Abonnés enregistrés</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Anomalies en attente</div>
            <div class="stat-value"><?= (int) $dashboard['nb_anomalies'] ?></div>
            <div class="stat-note">Relevés à vérifier</div>
        </div>
    </div>

    <section class="card">
        <div class="card-head">
            <div>
                <h2>Évolution mensuelle des consommations</h2>
                <p class="muted">Volumes facturés par mois pour l’année <?= $annee ?>.</p>
            </div>
            <span class="muted">
                Toutes années : <?= e(format_nombre($globaux['impaye'])) ?> DH impayés
            </span>
        </div>

        <?php if (!$aDesDonnees): ?>
            <div class="empty-state">Aucune donnée pour la période.</div>
        <?php else: ?>
            <div class="chart-grid">
                <div class="chart-card">
                    <div class="chart-head">
                        <h3>Eau</h3>
                        <span class="muted">max <?= e(format_nombre($dashboard['max_eau'])) ?> m³</span>
                    </div>
                    <?= chart_barres($dashboard['evolution'], 'eau', 'm³', '#0b6bcb', (float) $dashboard['max_eau']) ?>
                </div>

                <div class="chart-card">
                    <div class="chart-head">
                        <h3>Électricité</h3>
                        <span class="muted">max <?= e(format_nombre($dashboard['max_elec'])) ?> kWh</span>
                    </div>
                    <?= chart_barres($dashboard['evolution'], 'elec', 'kWh', '#f7b500', (float) $dashboard['max_elec']) ?>
                </div>
            </div>
        <?php endif; ?>
    </section>

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
