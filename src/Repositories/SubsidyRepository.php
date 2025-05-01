<?php

namespace Adess\EventManager\Repositories;

use Adess\EventManager\Models\Subsidy;

// Class SubsidyRepository : gère les opérations CRUD sur les subventions via PDO
class SubsidyRepository
{
    // Objet PDO pour la connexion à la base
    private $pdo;
    // Nom de la table (avec préfixe WP)
    private $table;

    // Constructeur : initialise la connexion PDO et définit le nom de la table
    public function __construct()
    {
        // 1) Récupère les constantes de connexion définies dans wp-config.php
        $host    = DB_HOST;
        $db      = DB_NAME;
        $user    = DB_USER;
        $pass    = DB_PASSWORD;
        $charset = 'utf8mb4'; // jeu de caractères Unicode complet

        // 2) Construit le DSN (Data Source Name) pour PDO
        $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
        // 3) Options de connexion : gestion d'erreurs, format de résultat, mode PREPARE
        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION, // exceptions sur erreurs SQL
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,       // résultats sous forme de tableau associatif
            \PDO::ATTR_EMULATE_PREPARES   => false,                   // vrais prepared statements
        ];

        // 4) Création de l'objet PDO (ouverture de la connexion)
        $this->pdo = new \PDO($dsn, $user, $pass, $options);
        // 5) Détermine le nom de la table avec le préfixe WordPress
        $this->table = $GLOBALS['wpdb']->prefix . 'adess_subsidies';
    }

    // Méthode find : récupère une subvention par son ID
    public function find(int $id): ?Subsidy
    {
        // Prépare la requête avec un placeholder sécurisé
        $sql  = "SELECT * FROM `{$this->table}` WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        // Exécution avec liaison de la valeur
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        // Si on a un résultat, on instancie un modèle Subsidy, sinon on retourne null
        return $row ? new Subsidy($row) : null;
    }

    // Méthode findAll : récupère toutes les subventions
    public function findAll(): array
    {
        $sql  = "SELECT * FROM `{$this->table}`";
        // PDO::query pour une requête simple sans placeholder
        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll();
        // Map chaque ligne à une instance de Subsidy
        return array_map(fn($row) => new Subsidy($row), $rows);
    }

    // Méthode countAll : compte le nombre total de subventions
    public function countAll(): int
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}`";
        // fetchColumn() récupère la première colonne de la première ligne
        return (int) $this->pdo->query($sql)->fetchColumn();
    }

    // Méthode findByPage : récupère une page de subventions (pagination)
    public function findByPage(int $perPage, int $offset): array
    {
        $sql  = "SELECT * FROM `{$this->table}` ORDER BY created_at DESC LIMIT :offset, :perPage";
        $stmt = $this->pdo->prepare($sql);
        // bindValue pour garantir le type entier
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->bindValue(':perPage', $perPage, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return array_map(fn($row) => new Subsidy($row), $rows);
    }

    // Méthode findByOrganizer : récupère les subventions liées à un organisateur donné
    public function findByOrganizer(int $organizerId): array
    {
        $sql  = "SELECT * FROM `{$this->table}` WHERE organizer_id = :organizerId ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':organizerId' => $organizerId]);
        $rows = $stmt->fetchAll();
        return array_map(fn($row) => new Subsidy($row), $rows);
    }

    // Méthode save : insère une nouvelle subvention ou met à jour une existante
    public function save(Subsidy $subsidy): int
    {
        if ($subsidy->getId() === null) {
            // Cas INSERT
            $sql = "INSERT INTO `{$this->table}`
                (organizer_id, total_amount, remaining_amount)
                VALUES (:organizer_id, :total_amount, :remaining_amount)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':organizer_id'     => $subsidy->getOrganizerId(),
                ':total_amount'     => $subsidy->getTotalAmount(),
                ':remaining_amount' => $subsidy->getRemainingAmount(),
            ]);
            // Retourne l'ID généré
            return (int) $this->pdo->lastInsertId();
        }

        // Cas UPDATE
        $sql = "UPDATE `{$this->table}` SET
            organizer_id      = :organizer_id,
            total_amount      = :total_amount,
            remaining_amount  = :remaining_amount
            WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':organizer_id'     => $subsidy->getOrganizerId(),
            ':total_amount'     => $subsidy->getTotalAmount(),
            ':remaining_amount' => $subsidy->getRemainingAmount(),
            ':id'               => $subsidy->getId(),
        ]);
        // Retourne l'ID modifié
        return $subsidy->getId();
    }

    // Méthode delete : supprime une subvention par son ID
    public function delete(int $id): int
    {
        $sql  = "DELETE FROM `{$this->table}` WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        // rowCount() indique le nombre de lignes touchées
        return $stmt->rowCount();
    }
}
