<?php

namespace Adess\EventManager\Repositories;

use Adess\EventManager\Models\Reservation;


// Le CRUD (Create, Read, Update, Delete) est géré par cette classe
class ReservationRepository
{
    // PDO instance used to interact with the database
    private $pdo;
    // Name of the reservations table (with WP prefix)
    private $table;

    // Constructor: establish the PDO connection and set the table name
    public function __construct()
    {
        // Grab DB connection settings from wp-config.php constants
        $host    = DB_HOST;
        $db      = DB_NAME;
        $user    = DB_USER;
        $pass    = DB_PASSWORD;
        $charset = 'utf8mb4';

        // Build the DSN string: specifies host, database, and charset
        $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

        // PDO options:
        //  - ERRMODE_EXCEPTION to throw exceptions on error
        //  - DEFAULT_FETCH_MODE_ASSOC to return results as associative arrays
        //  - EMULATE_PREPARES false to use real prepared statements
        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        // Create the PDO object (connect to the database)
        // cration de l'objet PDO
        
        $this->pdo   = new \PDO($dsn, $user, $pass, $options);
        // Set the table name, including WP prefix
        $this->table = $GLOBALS['wpdb']->prefix . 'adess_reservations';
    }

    // retourne une réservation par son ID
    public function find(int $id): ?Reservation
    {
        $sql  = "SELECT * FROM `{$this->table}` WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row  = $stmt->fetch();
        // If found, return a Reservation model, otherwise null
        return $row ? new Reservation($row) : null;
    }

    // Recupère toutes les réservations
    public function findAll(): array
    {
        $sql  = "SELECT * FROM `{$this->table}`";
        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll();
        // Map each row to a Reservation object
        return array_map(fn($row) => new Reservation($row), $rows);
    }

    // Count total number of reservations
    public function countAll(): int
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}`";
        // fetchColumn returns the first column of the first row
        return (int) $this->pdo->query($sql)->fetchColumn();
    }

    // Retrieve a subset of reservations for pagination
    public function findByPage(int $perPage, int $offset): array
    {
        $sql  = "SELECT * FROM `{$this->table}` ORDER BY created_at DESC LIMIT :offset, :perPage";
        $stmt = $this->pdo->prepare($sql);
        // Bind offset and limit as integers
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->bindValue(':perPage', $perPage, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return array_map(fn($row) => new Reservation($row), $rows);
    }

    // Insert a new reservation or update an existing one
    public function save(Reservation $reservation): int
    {
        if ($reservation->getId() === null) {
            // No ID => insert new record
            $sql = "INSERT INTO `{$this->table}`
                (event_id, user_id, guest_email, places, amount_paid, status)
                VALUES
                (:event_id, :user_id, :guest_email, :places, :amount_paid, :status)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':event_id'    => $reservation->getEventId(),
                ':user_id'     => $reservation->getUserId(),
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
            guest_email = :guest_email,
            places      = :places,
            amount_paid = :amount_paid,
            status      = :status
            WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':event_id'    => $reservation->getEventId(),
            ':user_id'     => $reservation->getUserId(),
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
