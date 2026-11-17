<?php
/**
 * @var array $client
 * @var int $nbConsommations
 * @var array<int, array<string, mixed>> $contrats
 * @var array<int, array<string, mixed>> $quartiers
 * @var string|null $erreur
 * @var string|null $succes
 * @var string $csrf
 */
?>
<?php require ROOT . 'IHM/layout/header.php'; ?>

<div class="page-head">
    <div>
        <h1><?= e($client['prenom'] . ' ' . $client['nom']) ?></h1>
        <p>Fiche client et suivi des consommations.</p>
    </div>
    <div class="page-head-actions">
        <a class="btn" href="<?= e(url('Client/update/' . (int) $client['id_client'])) ?>">
            Modifier ce client
        </a>
        <a class="btn btn-secondary" href="<?= e(url('Client/all')) ?>">
            Retour à la liste
        </a>
    </div>
</div>

<?php if (!empty($erreur)): ?>
    <div class="message-error"><?= e($erreur) ?></div>
<?php endif; ?>

<?php if (!empty($succes)): ?>
    <div class="message-success"><?= e($succes) ?></div>
<?php endif; ?>

<section class="card">
    <table class="table">
        <tr>
            <th>CIN</th>
            <td><?= e($client['cin']) ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?= e($client['email']) ?></td>
        </tr>
        <tr>
            <th>Téléphone</th>
            <td><?= e($client['telephone'] ?? '—') ?></td>
        </tr>
        <tr>
            <th>Adresse</th>
            <td><?= e($client['adresse']) ?></td>
        </tr>
        <tr>
            <th>Fournisseur</th>
            <td><?= e($client['fournisseur_nom'] ?? '—') ?></td>
        </tr>
        <tr>
            <th>Date de création</th>
            <td><?= e($client['date_creation']) ?></td>
        </tr>
        <tr>
            <th>Consommations enregistrées</th>
            <td><?= (int) $nbConsommations ?></td>
        </tr>
    </table>

</section>

<section class="card">
    <div class="invoice-head" style="margin-bottom: 1rem;">
        <h2 style="font-size: 1rem;">Contrats</h2>
        <span class="badge"><?= count($contrats) ?></span>
    </div>

    <?php if ($contrats === []): ?>
        <div class="empty-state">Aucun contrat pour ce client.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Numéro</th>
                        <th>Quartier</th>
                        <th>Activité</th>
                        <th>Début</th>
                        <th>Fin</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($contrats as $contrat): ?>
                    <tr>
                        <td class="nowrap"><?= e($contrat['numero_contrat']) ?></td>
                        <td><?= e($contrat['quartier_nom']) ?></td>
                        <td><?= e($contrat['type_activite']) ?></td>
                        <td><?= e(format_date_fr($contrat['date_debut'])) ?></td>
                        <td><?= e(format_date_fr($contrat['date_fin'])) ?></td>
                        <td>
                            <span class="badge <?= $contrat['statut'] === 'ACTIF' ? 'badge-open' : 'badge-danger' ?>">
                                <?= e($contrat['statut']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($contrat['statut'] === 'ACTIF'): ?>
                                <form method="post"
                                      action="<?= e(url('Contrat/resilier/' . (int) $contrat['id_contrat'])) ?>"
                                      class="notification-action-form">
                                    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                                    <button class="button button-secondary" type="submit">Résilier</button>
                                </form>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php if ($quartiers === []): ?>
        <div class="message-warning" style="margin-top: 1rem;">
            Créez d’abord un quartier depuis la page <a href="<?= e(url('Quartier/all')) ?>">Quartiers</a>.
        </div>
    <?php else: ?>
        <h3 style="margin-top: 1.2rem;">Ajouter un contrat</h3>
        <form method="post" action="<?= e(url('Contrat/add/' . (int) $client['id_client'])) ?>" class="form">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <div class="form-grid">
                <div class="form-group">
                    <label for="numero_contrat">Numéro de contrat</label>
                    <input type="text" id="numero_contrat" name="numero_contrat" maxlength="50" required>
                </div>
                <div class="form-group">
                    <label for="id_quartier">Quartier</label>
                    <select id="id_quartier" name="id_quartier" required>
                        <option value="">— Choisir un quartier —</option>
                        <?php foreach ($quartiers as $quartier): ?>
                            <option value="<?= (int) $quartier['id_quartier'] ?>">
                                <?= e($quartier['nom']) ?> (<?= e($quartier['type_activite']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="date_debut">Date de début</label>
                    <input type="date" id="date_debut" name="date_debut" value="<?= e(date('Y-m-d')) ?>" required>
                </div>
                <div class="form-group">
                    <label for="date_fin">Date de fin (facultative)</label>
                    <input type="date" id="date_fin" name="date_fin">
                </div>
            </div>
            <div class="form-actions">
                <button class="button" type="submit">Ajouter le contrat</button>
            </div>
        </form>
    <?php endif; ?>
</section>

<?php require ROOT . 'IHM/layout/footer.php'; ?>
