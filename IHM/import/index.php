<?php
/**
 * @var array|null $rapport
 * @var string|null $erreur
 * @var array $historique
 * @var int $nbTotal
 * @var array $annees
 */
$nomAgent = trim((string) (($_SESSION['prenom'] ?? '') . ' ' . ($_SESSION['nom'] ?? '')));
?>
<?php require ROOT . 'IHM/layout/header.php'; ?>

<div class="page-head">
    <div>
        <h1>Import annuel des consommations</h1>
        <p>
            Déposez le fichier <strong>Consommation_Annuelle.txt</strong> relevé lors de la tournée annuelle.
            L’import est automatique et un rapport indique les lignes insérées, ignorées et rejetées.
        </p>
    </div>
</div>

<?php if (!empty($erreur)): ?>
    <div class="message-error"><?= e($erreur) ?></div>
<?php endif; ?>

<?php if (is_array($rapport)): ?>
    <section class="card">
        <h2>Rapport d’import — <?= e($rapport['fichier']) ?></h2>
        <p class="muted">
            <?= (int) $rapport['nb_lignes'] ?> ligne<?= (int) $rapport['nb_lignes'] > 1 ? 's' : '' ?> analysée<?= (int) $rapport['nb_lignes'] > 1 ? 's' : '' ?>
            dans le fichier déposé.
        </p>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-label">Insérés</div>
                <div class="stat-value"><?= (int) $rapport['nb_inseres'] ?></div>
                <div class="stat-note">Relevés annuels enregistrés</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Ignorés</div>
                <div class="stat-value"><?= (int) $rapport['nb_ignores'] ?></div>
                <div class="stat-note">Doublons déjà importés</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Rejetés</div>
                <div class="stat-value"><?= (int) $rapport['nb_rejetes'] ?></div>
                <div class="stat-note">Lignes invalides</div>
            </div>
        </div>

        <?php if ($rapport['inseres'] !== []): ?>
            <details class="import-details" open>
                <summary>Lignes insérées (<?= (int) $rapport['nb_inseres'] ?>)</summary>
                <ul class="import-log import-ok">
                    <?php foreach ($rapport['inseres'] as $message): ?>
                        <li><?= e($message) ?></li>
                    <?php endforeach; ?>
                </ul>
            </details>
        <?php endif; ?>

        <?php if ($rapport['ignores'] !== []): ?>
            <details class="import-details">
                <summary>Lignes ignorées (<?= (int) $rapport['nb_ignores'] ?>)</summary>
                <ul class="import-log import-warning">
                    <?php foreach ($rapport['ignores'] as $message): ?>
                        <li><?= e($message) ?></li>
                    <?php endforeach; ?>
                </ul>
            </details>
        <?php endif; ?>

        <?php if ($rapport['rejetes'] !== []): ?>
            <details class="import-details">
                <summary>Lignes rejetées (<?= (int) $rapport['nb_rejetes'] ?>)</summary>
                <ul class="import-log import-error">
                    <?php foreach ($rapport['rejetes'] as $message): ?>
                        <li><?= e($message) ?></li>
                    <?php endforeach; ?>
                </ul>
            </details>
        <?php endif; ?>
    </section>
<?php endif; ?>

<section class="card">
    <h2>Déposer le fichier annuel</h2>
    <p class="muted">
        Fichier texte séparé par des virgules, 2 Mo maximum.
        L’en-tête est obligatoire et doit reprendre exactement les six colonnes ci-dessous.
    </p>

    <pre class="code-sample">ID_Client,ID_Agent,ConsommationEau,ConsommationElect,Annee,Date_Saisie
1,1,120.50,340.00,2026,2026-12-15
2,1,85.00,260.00,2026,2026-12-15</pre>

    <form class="form" method="post" action="<?= e(url('Import/upload')) ?>" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <div class="form-group">
            <label for="fichier">Fichier Consommation_Annuelle.txt</label>
            <input type="file" id="fichier" name="fichier" accept=".txt,text/plain" required>
        </div>

        <div class="form-actions">
            <button class="button" type="submit">Importer le fichier</button>
            <span class="muted">Agent : <?= e($nomAgent !== '' ? $nomAgent : 'Agent') ?></span>
        </div>
    </form>
</section>

<section class="card" id="releves">
    <h2>Relevés importés</h2>
    <p class="muted">
        <?= count($historique) ?> relevé<?= count($historique) > 1 ? 's' : '' ?> déposé<?= count($historique) > 1 ? 's' : '' ?> par cet agent
        · <?= $nbTotal ?> relevé<?= $nbTotal > 1 ? 's' : '' ?> au total en base
        <?= $annees !== [] ? ' · années : ' . e(implode(', ', $annees)) : '' ?>
    </p>

    <?php if ($historique === []): ?>
        <div class="empty-state">
            Aucun relevé annuel enregistré pour le moment. Déposez le fichier de la tournée annuelle.
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Année</th>
                        <th>Eau</th>
                        <th>Électricité</th>
                        <th>Date de saisie</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($historique as $releve): ?>
                    <tr>
                        <td class="client-cell">
                            <strong><?= e($releve['nom'] . ' ' . $releve['prenom']) ?></strong>
                            <small><?= e($releve['cin']) ?></small>
                        </td>
                        <td><?= (int) $releve['annee'] ?></td>
                        <td><?= e(format_nombre((float) $releve['conso_eau'])) ?> m³</td>
                        <td><?= e(format_nombre((float) $releve['conso_elec'])) ?> kWh</td>
                        <td><?= e(format_date_fr($releve['date_saisie'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require ROOT . 'IHM/layout/footer.php'; ?>
