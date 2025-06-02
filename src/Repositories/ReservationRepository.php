<?php

namespace Adess\EventManager\Repositories;

use Adess\EventManager\Models\Reservation;


// Le CRUD (Create, Read, Update, Delete) est géré par cette classe
class ReservationRepository
{
    // PDo qui permet d'interagir avec la base de données
    private $pdo;
    // Nom de la table de réservation
    private $table;

    // Ce constructeur initialise la connexion à la base de données
    public function __construct()
    {
        // Recuperation des constantes de configuration WordPress pour la base de données
        $host    = DB_HOST;
        $db      = DB_NAME;
        $user    = DB_USER;
        $pass    = DB_PASSWORD;
        $charset = 'utf8mb4';

        // Le dsn (Data Source Name) est une chaîne de connexion pour PDO
        $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

        // Ici on configure PDO pour gérer les erreurs, le mode de récupération par défaut et la simulation des requêtes préparées
        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        // cration de l'objet PDO

        $this->pdo   = new \PDO($dsn, $user, $pass, $options);
        // configuration de la table
        $this->table = $GLOBALS['wpdb']->prefix . 'adess_reservations';
    }

    // retourne une réservation par son ID
    public function find(int $id): ?Reservation
    {
        $sql  = "SELECT * FROM `{$this->table}` WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row  = $stmt->fetch();
        // Si trouvé, on crée un objet Reservation
        return $row ? new Reservation($row) : null;
    }

    // Recupère toutes les réservations
    public function findAll(): array
    {
        $sql  = "SELECT * FROM `{$this->table}`";
        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll();
        // cherches toutes les réservations et les transforme en objets Reservation
        return array_map(fn($row) => new Reservation($row), $rows);
    }

    // Total des réservations
    public function countAll(): int
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}`";
        // rretourne le nombre total de réservations
        return (int) $this->pdo->query($sql)->fetchColumn();
    }

    // Compte le nombre de réservations pour un événement donné
    public function countByEventId(int $eventId): int
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}` WHERE event_id = :event_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':event_id' => $eventId]);
        return (int) $stmt->fetchColumn();
    }


    // Cette méthode récupère les réservations par page
    public function findByPage(int $perPage, int $offset): array
    {
        $sql  = "SELECT * FROM `{$this->table}` ORDER BY created_at DESC LIMIT :offset, :perPage";
        $stmt = $this->pdo->prepare($sql);
        // protection contre les injections SQL
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->bindValue(':perPage', $perPage, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return array_map(fn($row) => new Reservation($row), $rows);
    }

    // Insèret ou met à jour une réservation
    public function save(Reservation $reservation): int
    {
        if ($reservation->getId() === null) {
            // No ID => insère une nouvelle réservation
            $sql = "INSERT INTO `{$this->table}`
                (event_id, user_id, guest_name, guest_firstname, guest_postcode,guest_email, places, amount_paid, status)
                VALUES
                (:event_id, :user_id,  :guest_name, :guest_firstname, :guest_postcode,:guest_email, :places, :amount_paid, :status)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':event_id'    => $reservation->getEventId(),
                ':user_id'     => $reservation->getUserId(),
                ':guest_name'      => $reservation->getGuestName(),
                ':guest_firstname' => $reservation->getGuestFirstname(),
                ':guest_postcode'  => $reservation->getGuestPostcode(),
                ':guest_email' => $reservation->getGuestEmail(),
                ':places'      => $reservation->getPlaces(),
                ':amount_paid' => $reservation->getAmountPaid(),
                ':status'      => $reservation->getStatus(),
            ]);
            // Return the new record's ID
            return (int) $this->pdo->lastInsertId();
        }

        // Existing reservation => update record
        $sql = "UPDATE `{$this->table}` SET
            event_id    = :event_id,
            user_id     = :user_id,
            guest_name  = :guest_name,
            guest_firstname = :guest_firstname,
            guest_postcode = :guest_postcode,
            guest_email = :guest_email,
            places      = :places,
            amount_paid = :amount_paid,
            status      = :status
            WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':event_id'    => $reservation->getEventId(),
            ':user_id'     => $reservation->getUserId(),
            ':guest_name'      => $reservation->getGuestName(),
            ':guest_firstname' => $reservation->getGuestFirstname(),
            ':guest_postcode'  => $reservation->getGuestPostcode(),
            ':guest_email' => $reservation->getGuestEmail(),
            ':places'      => $reservation->getPlaces(),
            ':amount_paid' => $reservation->getAmountPaid(),
            ':status'      => $reservation->getStatus(),
            ':id'          => $reservation->getId(),
        ]);
        // Return the updated reservation's ID
        return $reservation->getId();
    }

    // Delete a reservation record by its ID
    public function delete(int $id): int
    {
        $sql  = "DELETE FROM `{$this->table}` WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        // rowCount() tells you how many rows were deleted
        return $stmt->rowCount();
    }
}
