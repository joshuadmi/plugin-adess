<?php

namespace Adess\EventManager\Repositories;

use Adess\EventManager\Models\Event;

// Opérations CRUD pour les événements en utilisant PDO
class EventRepository
{
    // Objet PDO pour se connecter à la base
    private $pdo;
    // Nom de la table
    private $table;

    public function __construct()
    {
        // Récupération des constantes WP pour la connexion
        $host = DB_HOST;
        $db   = DB_NAME;
        $user = DB_USER;
        $pass = DB_PASSWORD;
        $charset = 'utf8mb4';

        // DSN (Data Source Name) et options PDO
        $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION, // Exceptions en cas d'erreur
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,       // Résultats en tableaux associatifs
            \PDO::ATTR_EMULATE_PREPARES   => false,                   // Vrais prepared statements
        ];

        // Création de la connexion PDO
        $this->pdo   = new \PDO($dsn, $user, $pass, $options);
        // Préfixe WordPress avec nom de table personnalisée
        $this->table = $GLOBALS['wpdb']->prefix . 'adess_events';
    }

    // Retourne un événement par son ID
    public function find(int $id): ?Event
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ? new Event($row) : null;
    }

    // Récupère tous les événements
    public function findAll(): array
    {
        $sql = "SELECT * FROM `{$this->table}`";
        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll();
        return array_map(fn($row) => new Event($row), $rows);
    }

    // Compte le nombre total d'événements
    public function countAll(): int
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}`";
        return (int) $this->pdo->query($sql)->fetchColumn();
    }

    // Récupération paginée
    public function findByPage(int $perPage, int $offset): array
    {
        $sql = "SELECT * FROM `{$this->table}` ORDER BY start_date ASC LIMIT :offset, :perPage";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->bindValue(':perPage', $perPage, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return array_map(fn($row) => new Event($row), $rows);
    }

    // Insère ou met à jour un événement
    public function save(Event $event): int
    {
        if ($event->getId() === null) {
            // INSERT
            $sql = "INSERT INTO `{$this->table}`
                   (organizer_id, type, title, location, start_date,  participant_count, estimated_cost, subsidy_amount, notes, status)
                   VALUES
                   (:organizer_id, :type, :title, :location, :start_date,  :participant_count, :estimated_cost, :subsidy_amount, :notes, :status)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':organizer_id'      => $event->getOrganizerId(),
                ':type'              => $event->getType(),
                ':title'             => $event->getTitle(),
                ':location'          => $event->getLocation(),
                ':start_date'        => $event->getStartDate()->format('Y-m-d H:i:s'),
                ':participant_count' => $event->getParticipantCount(),
                ':estimated_cost'    => $event->getEstimatedCost(),
                ':subsidy_amount'    => $event->getSubsidyAmount(),
                ':notes'             => $event->getNotes(),
                ':status'            => $event->getStatus(),
            ]);
            return (int) $this->pdo->lastInsertId();
        }

        // UPDATE
        $sql = "UPDATE `{$this->table}` SET
                   organizer_id      = :organizer_id,
                   type              = :type,
                   title             = :title,
                   location          = :location,
                   start_date        = :start_date,
                   participant_count = :participant_count,
                   estimated_cost    = :estimated_cost,
                    subsidy_amount    = :subsidy_amount,
                   notes             = :notes,
                   status            = :status
                 WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':organizer_id'      => $event->getOrganizerId(),
            ':type'              => $event->getType(),
            ':title'             => $event->getTitle(),
            ':location'          => $event->getLocation(),
            ':start_date'        => $event->getStartDate()->format('Y-m-d H:i:s'),
            ':participant_count' => $event->getParticipantCount(),
            ':estimated_cost'    => $event->getEstimatedCost(),
            ':subsidy_amount'    => $event->getSubsidyAmount(),
            ':notes'             => $event->getNotes(),
            ':status'            => $event->getStatus(),
            ':id'                => $event->getId(),
        ]);
        return $event->getId();
    }

    // Supprime un événement par ID
    public function delete(int $id): int
    {
        $sql = "DELETE FROM `{$this->table}` WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }
}
