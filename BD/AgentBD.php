<?php
declare(strict_types=1);

/**
 * Acces aux agents (tournee annuelle et import du fichier
 * Consommation_Annuelle.txt).
 */
final class AgentBD
{
    public function __construct(private PDO $pdo) {}

    public function trouverParEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id_agent, nom, prenom, email, mot_de_passe, telephone
             FROM agent
             WHERE email = :email
             LIMIT 1'
        );
        $stmt->execute(['email' => $email]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function trouverParId(int $idAgent): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id_agent, nom, prenom, email, telephone, date_creation
             FROM agent
             WHERE id_agent = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $idAgent]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Identifiants existants, indexes par id : sert a valider la
     * colonne ID_Agent du fichier sans faire une requete par ligne.
     *
     * @return array<int, true>
     */
    public function idsExistants(): array
    {
        $ids = [];

        foreach ($this->pdo->query('SELECT id_agent FROM agent') as $ligne) {
            $ids[(int) $ligne['id_agent']] = true;
        }

        return $ids;
    }
}
