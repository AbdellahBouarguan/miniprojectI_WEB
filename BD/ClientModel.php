<?php

/**
 * Acces aux donnees de la table client.
 * Toutes les requetes sont preparees : aucune valeur n'est collee dans le SQL.
 */

function client_model_all(int $idFournisseur, string $search = ''): array
{
    $like = '%' . $search . '%';

    $sql = 'SELECT id_client, cin, nom, prenom, email, telephone, date_creation
            FROM client
            WHERE id_fournisseur = ?
              AND (cin LIKE ? OR nom LIKE ? OR prenom LIKE ? OR email LIKE ?)
            ORDER BY nom, prenom';

    $stmt = mysqli_prepare(db(), $sql);
    mysqli_stmt_bind_param($stmt, 'issss', $idFournisseur, $like, $like, $like, $like);
    mysqli_stmt_execute($stmt);

    return mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
}

function client_model_find(int $id): ?array
{
    $sql = 'SELECT id_client, id_fournisseur, cin, nom, prenom, adresse, email, telephone, date_creation
            FROM client
            WHERE id_client = ?';

    $stmt = mysqli_prepare(db(), $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);

    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    return $row ?: null;
}

/** Vrai si le CIN existe deja (en ignorant le client $exceptId lors d'une modification). */
function client_model_cin_exists(string $cin, int $exceptId = 0): bool
{
    $stmt = mysqli_prepare(db(), 'SELECT 1 FROM client WHERE cin = ? AND id_client <> ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'si', $cin, $exceptId);
    mysqli_stmt_execute($stmt);

    return mysqli_stmt_get_result($stmt)->num_rows > 0;
}

/** Vrai si l'email existe deja (en ignorant le client $exceptId lors d'une modification). */
function client_model_email_exists(string $email, int $exceptId = 0): bool
{
    $stmt = mysqli_prepare(db(), 'SELECT 1 FROM client WHERE email = ? AND id_client <> ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'si', $email, $exceptId);
    mysqli_stmt_execute($stmt);

    return mysqli_stmt_get_result($stmt)->num_rows > 0;
}

/** Cree un client et renvoie son id. Le mot de passe doit deja etre hache. */
function client_model_insert(int $idFournisseur, array $d, string $hash): int
{
    $sql = 'INSERT INTO client (id_fournisseur, cin, nom, prenom, adresse, email, mot_de_passe, telephone)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)';

    $stmt = mysqli_prepare(db(), $sql);
    mysqli_stmt_bind_param(
        $stmt,
        'isssssss',
        $idFournisseur,
        $d['cin'],
        $d['nom'],
        $d['prenom'],
        $d['adresse'],
        $d['email'],
        $hash,
        $d['telephone']
    );
    mysqli_stmt_execute($stmt);

    return (int) mysqli_insert_id(db());
}

/** Modifie les informations d'un client (sans toucher au mot de passe). */
function client_model_update(int $id, array $d): bool
{
    $sql = 'UPDATE client
            SET cin = ?, nom = ?, prenom = ?, adresse = ?, email = ?, telephone = ?
            WHERE id_client = ?';

    $stmt = mysqli_prepare(db(), $sql);
    mysqli_stmt_bind_param(
        $stmt,
        'ssssssi',
        $d['cin'],
        $d['nom'],
        $d['prenom'],
        $d['adresse'],
        $d['email'],
        $d['telephone'],
        $id
    );

    return mysqli_stmt_execute($stmt);
}

/** Change le mot de passe d'un client. Le mot de passe doit deja etre hache. */
/*function client_model_update_password(int $id, string $hash): bool
{
    $stmt = mysqli_prepare(db(), 'UPDATE client SET mot_de_passe = ? WHERE id_client = ?');
    mysqli_stmt_bind_param($stmt, 'si', $hash, $id);

    return mysqli_stmt_execute($stmt);
}*/
