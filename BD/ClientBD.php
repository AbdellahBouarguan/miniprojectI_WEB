<?php
declare(strict_types=1);

final class ClientBD
{
    public function __construct(private mysqli $db) {}

    public function lister(int $idFournisseur, string $search = ''): array
    {
        $sql = 'SELECT c.id_client, c.id_fournisseur, c.cin, c.nom, c.prenom,
                       c.adresse, c.email, c.telephone, c.date_creation,
                       f.nom AS fournisseur_nom,
                       (SELECT COUNT(*)
                        FROM consommation co
                        WHERE co.id_client = c.id_client) AS nb_conso
                FROM client c
                JOIN fournisseur f ON f.id_fournisseur = c.id_fournisseur
                WHERE c.id_fournisseur = ?';

        if ($search !== '') {
            $sql .= ' AND (c.cin LIKE ? OR c.nom LIKE ? OR c.prenom LIKE ? OR c.email LIKE ?)';
        }

        $sql .= ' ORDER BY c.nom, c.prenom';

        $stmt = mysqli_prepare($this->db, $sql);

        if ($search !== '') {
            $like = '%' . $search . '%';
            mysqli_stmt_bind_param($stmt, 'issss', $idFournisseur, $like, $like, $like, $like);
        } else {
            mysqli_stmt_bind_param($stmt, 'i', $idFournisseur);
        }

        mysqli_stmt_execute($stmt);

        $resultat = mysqli_stmt_get_result($stmt);

        return mysqli_fetch_all($resultat, MYSQLI_ASSOC);
    }

    public function trouver(int $idClient, int $idFournisseur): ?array
    {
        $sql = 'SELECT c.*, f.nom AS fournisseur_nom
                FROM client c
                JOIN fournisseur f ON f.id_fournisseur = c.id_fournisseur
                WHERE c.id_client = ? AND c.id_fournisseur = ?
                LIMIT 1';

        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, 'ii', $idClient, $idFournisseur);
        mysqli_stmt_execute($stmt);

        $resultat = mysqli_stmt_get_result($stmt);
        $client = mysqli_fetch_assoc($resultat);

        return $client ?: null;
    }

    public function emailExiste(string $email, ?int $saufId = null): bool
    {
        $sql = 'SELECT 1 FROM client WHERE email = ?';

        if ($saufId !== null) {
            $sql .= ' AND id_client <> ?';
        }

        $sql .= ' LIMIT 1';

        $stmt = mysqli_prepare($this->db, $sql);

        if ($saufId !== null) {
            mysqli_stmt_bind_param($stmt, 'si', $email, $saufId);
        } else {
            mysqli_stmt_bind_param($stmt, 's', $email);
        }

        mysqli_stmt_execute($stmt);
        $resultat = mysqli_stmt_get_result($stmt);

        return (bool) mysqli_fetch_row($resultat);
    }

    public function cinExiste(string $cin, ?int $saufId = null): bool
    {
        $sql = 'SELECT 1 FROM client WHERE cin = ?';

        if ($saufId !== null) {
            $sql .= ' AND id_client <> ?';
        }

        $sql .= ' LIMIT 1';

        $stmt = mysqli_prepare($this->db, $sql);

        if ($saufId !== null) {
            mysqli_stmt_bind_param($stmt, 'si', $cin, $saufId);
        } else {
            mysqli_stmt_bind_param($stmt, 's', $cin);
        }

        mysqli_stmt_execute($stmt);
        $resultat = mysqli_stmt_get_result($stmt);

        return (bool) mysqli_fetch_row($resultat);
    }

    public function creer(int $idFournisseur, array $client): bool
    {
        $sql = 'INSERT INTO client
                    (id_fournisseur, cin, nom, prenom, adresse, email,
                     mot_de_passe, telephone)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)';

        $cin = $client['cin'];
        $nom = $client['nom'];
        $prenom = $client['prenom'];
        $adresse = $client['adresse'];
        $email = $client['email'];
        $motDePasse = $client['mot_de_passe'];
        $telephone = $client['telephone'] !== '' ? $client['telephone'] : null;

        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param(
            $stmt,
            'isssssss',
            $idFournisseur,
            $cin,
            $nom,
            $prenom,
            $adresse,
            $email,
            $motDePasse,
            $telephone
        );

        return mysqli_stmt_execute($stmt);
    }

    public function mettreAJour(int $idClient, int $idFournisseur, array $client): bool
    {
        $sql = 'UPDATE client
                SET cin = ?, nom = ?, prenom = ?, adresse = ?, email = ?,
                    mot_de_passe = ?, telephone = ?
                WHERE id_client = ? AND id_fournisseur = ?';

        $cin = $client['cin'];
        $nom = $client['nom'];
        $prenom = $client['prenom'];
        $adresse = $client['adresse'];
        $email = $client['email'];
        $motDePasse = $client['mot_de_passe'];
        $telephone = $client['telephone'] !== '' ? $client['telephone'] : null;

        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param(
            $stmt,
            'sssssssii',
            $cin,
            $nom,
            $prenom,
            $adresse,
            $email,
            $motDePasse,
            $telephone,
            $idClient,
            $idFournisseur
        );

        return mysqli_stmt_execute($stmt);
    }

    public function supprimer(int $idClient, int $idFournisseur): bool
    {
        $sql = 'DELETE FROM client WHERE id_client = ? AND id_fournisseur = ?';

        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, 'ii', $idClient, $idFournisseur);

        return mysqli_stmt_execute($stmt);
    }

    public function nbConsommations(int $idClient): int
    {
        $stmt = mysqli_prepare(
            $this->db,
            'SELECT COUNT(*) FROM consommation WHERE id_client = ?'
        );
        mysqli_stmt_bind_param($stmt, 'i', $idClient);
        mysqli_stmt_execute($stmt);

        $resultat = mysqli_stmt_get_result($stmt);
        $ligne = mysqli_fetch_row($resultat);

        return (int) ($ligne[0] ?? 0);
    }
}
