<?php
namespace Adess\EventManager\Repositories;

use Adess\EventManager\Models\Organizer;

// Opérations CRUD pour les organisateurs en utilisant PDO
class OrganizerRepository
{
    // Objet PDO pour se connecter à la base
    private $pdo;
    // Nom de la table
    private $table;

    public function __construct()
    {
        // Récupération des constantes WP pour la connexion
        $host    = DB_HOST;
        $db      = DB_NAME;
        $user    = DB_USER;
        $pass    = DB_PASSWORD;
        $charset = 'utf8mb4';

        // DSN (Data Source Name) et options PDO
        $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION, // Exceptions en cas d'erreur SQL
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,       // Résultats en tableaux associatifs
            \PDO::ATTR_EMULATE_PREPARES   => false,                   // Vrais prepared statements
        ];

        // Création de la connexion PDO
        $this->pdo   = new \PDO($dsn, $user, $pass, $options);
        // Table avec préfixe WordPress
        $this->table = $GLOBALS['wpdb']->prefix . 'adess_organizers';
    }

    // Enregistre un organisateur (insert ou update)
    public function save(Organizer $organizer): int
    {
        if ($organizer->getId() === null) {
            // INSERT
            $sql = "INSERT INTO `{$this->table}`
                    (user_id, type, name, address, contact_name, contact_email, phone, default_location, status)
                     VALUES (:user_id, :type, :name, :address, :contact_name, :contact_email, :phone, :default_location, :status)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':user_id'          => $organizer->getUserId(),
                ':type'             => $organizer->getType(),
                ':name'             => $organizer->getName(),
                ':address'          => $organizer->getAddress(),
                ':contact_name'     => $organizer->getContactName(),
                ':contact_email'    => $organizer->getContactEmail(),
                ':phone'            => $organizer->getPhone(),
                ':default_location' => $organizer->getDefaultLocation(),
                ':status'           => $organizer->getStatus(),
            ]);
            // Retourne l'ID inséré
            return (int) $this->pdo->lastInsertId();
        }

        // UPDATE
        $sql = "UPDATE `{$this->table}` SET
                    user_id          = :user_id,
                    type             = :type,
                    name             = :name,
                    address          = :address,
                    contact_name     = :contact_name,
                    contact_email    = :contact_email,
                    phone            = :phone,
                    default_location = :default_location,
                    status           = :status
                 WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_id'          => $organizer->getUserId(),
            ':type'             => $organizer->getType(),
            ':name'             => $organizer->getName(),
            ':address'          => $organizer->getAddress(),
            ':contact_name'     => $organizer->getContactName(),
            ':contact_email'    => $organizer->getContactEmail(),
            ':phone'            => $organizer->getPhone(),
            ':default_location' => $organizer->getDefaultLocation(),
            ':status'           => $organizer->getStatus(),
            ':id'               => $organizer->getId(),
        ]);
        // Retourne l'ID mis à jour
        return $organizer->getId();
    }

    // Récupère un organisateur par son ID
    public function find(int $id): ?Organizer
    {
        $sql  = "SELECT * FROM `{$this->table}` WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? new Organizer($row) : null;
    }

    // Récupère un organisateur via l'ID WP de l'utilisateur
    public function findByUserId(int $userId): ?Organizer
    {
        $sql  = "SELECT * FROM `{$this->table}` WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ? new Organizer($row) : null;
    }

    // Compte tous les organisateurs
    public function countAll(): int
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}`";
        return (int) $this->pdo->query($sql)->fetchColumn();
    }

    // Récupère une page d'organisateurs pour la pagination
    public function findByPage(int $perPage, int $offset): array
    {
        $sql  = "SELECT * FROM `{$this->table}` ORDER BY created_at DESC LIMIT :offset, :perPage";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->bindValue(':perPage', $perPage, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return array_map(fn($row) => new Organizer($row), $rows);
    }

    // Supprime un organisateur par son ID
    public function delete(int $id): int
    {
        $sql  = "DELETE FROM `{$this->table}` WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }
}
