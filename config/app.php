<?php

define('APP_NAME', 'AquaVolt');
define('APP_TAGLINE', 'Eau & Électricité · Factures');
define('APP_SUBTITLE', 'Mini Projet 1 - Version Alpha');

// Base URL detectee automatiquement : fonctionne dans /miniprojectI_WEB-1/
// comme a la racine du serveur PHP integre.
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
$basePath = dirname($scriptName);
$basePath = ($basePath === '/' || $basePath === '.') ? '' : rtrim($basePath, '/');

define('BASE_URL', $basePath . '/');
define('TVA_RATE', 18.00);
define('TAUX_ANOMALIE', 0.40);
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);
define('UPLOAD_DIR', ROOT . 'uploads/compteurs/');
define('UPLOAD_URL', BASE_URL . 'uploads/compteurs/');

function url(string $path = ''): string
{
    return BASE_URL . ltrim($path, '/');
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function role_label(?string $role): string
{
    return match ($role) {
        'client' => 'Client',
        'fournisseur' => 'Fournisseur',
        default => 'Visiteur',
    };
}

function initials(string $prenom, string $nom): string
{
    $prenom = trim($prenom);
    $nom = trim($nom);

    $premiereLettre = static function (string $texte): string {
        if ($texte === '') {
            return '';
        }

        return function_exists('mb_substr')
            ? mb_substr($texte, 0, 1, 'UTF-8')
            : substr($texte, 0, 1);
    };

    $initiales = strtoupper(
        $premiereLettre($prenom) . $premiereLettre($nom)
    );

    return $initiales !== '' ? $initiales : 'AV';
}

function mois_francais(int $mois): string
{
    $noms = [
        1 => 'Janvier',
        2 => 'Février',
        3 => 'Mars',
        4 => 'Avril',
        5 => 'Mai',
        6 => 'Juin',
        7 => 'Juillet',
        8 => 'Août',
        9 => 'Septembre',
        10 => 'Octobre',
        11 => 'Novembre',
        12 => 'Décembre',
    ];

    return $noms[$mois] ?? '';
}

function format_date_fr(?string $date): string
{
    if ($date === null || $date === '') {
        return '—';
    }

    $timestamp = strtotime($date);

    return $timestamp !== false ? date('d/m/Y', $timestamp) : '—';
}

/**
 * Formate un nombre sans zéros inutiles : 142.00 -> 142, 198.80 -> 198,8.
 */
function format_nombre(float $valeur, int $decimales = 2): string
{
    $texte = number_format($valeur, $decimales, ',', ' ');

    if ($decimales > 0) {
        $texte = rtrim(rtrim($texte, '0'), ',');
    }

    return $texte === '' ? '0' : $texte;
}

/**
 * Seuil de vigilance exprime en index.
 * La maquette applique +40 % par rapport a l'index precedent,
 * avec le seuil du fournisseur comme plancher.
 */
function seuil_effectif(float $seuilFournisseur, ?float $indexPrecedent): float
{
    if ($indexPrecedent === null) {
        return round(max(0.0, $seuilFournisseur), 2);
    }

    return round(
        max(
            $seuilFournisseur,
            $indexPrecedent * (1 + TAUX_ANOMALIE)
        ),
        2
    );
}

function icon(string $name, string $class = 'icon'): string
{
    $paths = [
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>',
        'file' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
        'droplet' => '<path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>',
        'zap' => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
        'users' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'message' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
        'alert' => '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
        'card' => '<rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>',
        'check' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
        'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
        'bell' => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
        'mail' => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
        'lock' => '<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'eye' => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
        'camera' => '<path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/>',
        'plus' => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
        'refresh' => '<polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>',
        'arrow-right' => '<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>',
        'user' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'send' => '<line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>',
        'chevron-right' => '<polyline points="9 18 15 12 9 6"/>',
        'edit' => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4z"/>',
        'trash' => '<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
    ];

    $path = $paths[$name] ?? '';

    return '<svg class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8')
        . '" width="20" height="20" viewBox="0 0 24 24" fill="none"'
        . ' stroke="currentColor" stroke-width="1.8" stroke-linecap="round"'
        . ' stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
}

function brand_mark(): string
{
    return '<svg class="brand-mark" width="38" height="38" viewBox="0 0 40 40" aria-hidden="true">'
        . '<path d="M20 3C20 3 8 16.2 8 24a12 12 0 0 0 24 0C32 16.2 20 3 20 3Z" fill="#0b6bcb"/>'
        . '<path d="M22 13l-8 12h6l-1 9 9-13h-6l1-8z" fill="#f7b500"/>'
        . '</svg>';
}
