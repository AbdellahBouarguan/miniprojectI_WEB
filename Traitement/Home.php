<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../BD/Connexion.php';
require_once __DIR__ . '/../BD/Dashboard.php';

/**
 * Accueil / tableau de bord.
 *
 * - visiteur : page publique ;
 * - client   : ses indicateurs (calcules dans la vue) ;
 * - agent    : redirige vers son espace d'import ;
 * - fournisseur : tableau de bord impayes + evolution mensuelle.
 */
function home_index(): void
{
    $role = $_SESSION['role'] ?? null;

    if ($role === 'agent') {
        header('Location: ' . url('Import/index'));
        exit;
    }

    $dashboard = null;

    if ($role === 'fournisseur') {
        $dashboard = home_dashboard_fournisseur((int) ($_SESSION['id_fournisseur'] ?? 0));
    }

    require ROOT . 'IHM/home.php';
}

/**
 * Prepare les indicateurs du tableau de bord fournisseur.
 *
 * @return array{
 *     annee: int,
 *     annees: array<int, int>,
 *     totaux: array<string, float|int>,
 *     totaux_globaux: array<string, float|int>,
 *     evolution: array<int, array<string, mixed>>,
 *     nb_clients: int,
 *     nb_anomalies: int,
 *     max_eau: float,
 *     max_elec: float
 * }
 */
function home_dashboard_fournisseur(int $idFournisseur): array
{
    $bd = new DashboardBD(pdo());

    $annees = $bd->anneesDisponibles($idFournisseur);
    $annee = (int) ($_GET['annee'] ?? 0);

    if (!in_array($annee, $annees, true)) {
        $annee = $annees[0] ?? (int) date('Y');
    }

    $evolution = $bd->evolutionMensuelle($idFournisseur, $annee);

    $maxEau = 0.0;
    $maxElec = 0.0;

    foreach ($evolution as $mois) {
        $maxEau = max($maxEau, (float) $mois['eau']);
        $maxElec = max($maxElec, (float) $mois['elec']);
    }

    return [
        'annee' => $annee,
        'annees' => $annees,
        'totaux' => $bd->totaux($idFournisseur, $annee),
        'totaux_globaux' => $bd->totaux($idFournisseur),
        'evolution' => $evolution,
        'nb_clients' => $bd->nbClients($idFournisseur),
        'nb_anomalies' => $bd->nbAnomaliesEnAttente($idFournisseur),
        'max_eau' => $maxEau,
        'max_elec' => $maxElec,
    ];
}
