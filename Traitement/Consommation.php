<?php
declare(strict_types=1);

require_once __DIR__ . '/ConsommationTraitement.php';

/**
 * Point d'entree appele par le routeur (index.php).
 * Le routeur transforme /Consommation/add en fonction consommation_add().
 */
function consommation_controleur(): Consommation
{
    return new Consommation(pdo());
}

function consommation_add($id = null): void
{
    if ($id !== null && $id !== '') {
        $_GET['id_conso'] = (int) $id;
    }

    $controleur = consommation_controleur();

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $controleur->enregistrer();
        return;
    }

    $controleur->afficherSaisie();
}

function consommation_all($id = null): void
{
    consommation_controleur()->historique();
}

function consommation_historique($id = null): void
{
    consommation_controleur()->historique();
}

function consommation_one($id = null): void
{
    $_GET['id_conso'] = (int) ($id ?? $_GET['id_conso'] ?? 0);
    consommation_controleur()->detail();
}

function consommation_detail($id = null): void
{
    consommation_one($id);
}

function consommation_confirmation($id = null): void
{
    $_GET['id_conso'] = (int) ($id ?? $_GET['id_conso'] ?? 0);
    consommation_controleur()->afficherConfirmation();
}

function consommation_confirmer($id = null): void
{
    consommation_controleur()->confirmer();
}

function consommation_facturer($id = null): void
{
    consommation_controleur()->facturer();
}
