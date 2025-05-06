<?php

namespace Adess\EventManager;

class Installer
{
    // Méthode exécutée à l'activation du plugin
    public static function activate()
    {
        // Connexion PDO manuelle pour s'affranchir du couplage avec WordPress
        $host    = DB_HOST;
        $db      = DB_NAME;
        $user    = DB_USER;
        $pass    = DB_PASSWORD;
        $charset = 'utf8mb4';

        $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $pdo = new \PDO($dsn, $user, $pass, $options);
        $prefix = $GLOBALS['wpdb']->prefix . 'adess_';

        // 1. Table des organisateurs
        $pdo->exec("CREATE TABLE IF NOT EXISTS {$prefix}organizers (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED DEFAULT NULL,
            type ENUM('company','collectivity') NOT NULL,
            name VARCHAR(191) NOT NULL,
            address TEXT NOT NULL,
            contact_name VARCHAR(191) DEFAULT NULL,
            contact_email VARCHAR(191) DEFAULT NULL,
            phone VARCHAR(50) DEFAULT NULL,
            default_location TEXT DEFAULT NULL,
            status ENUM('pending','validated','rejected') NOT NULL DEFAULT 'pending',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // 2. Table des événements
        $pdo->exec("CREATE TABLE IF NOT EXISTS {$prefix}events (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            organizer_id BIGINT UNSIGNED NOT NULL,
            title VARCHAR(191) NOT NULL,
            location TEXT,
            start_date DATETIME NOT NULL,
            participant_count INT UNSIGNED DEFAULT 0,
            estimated_cost DECIMAL(10,2) DEFAULT 0,
            notes TEXT,
            status ENUM('pending','validated','cancelled') NOT NULL DEFAULT 'pending',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // 3. Table des subventions
        $pdo->exec("CREATE TABLE IF NOT EXISTS {$prefix}subsidies (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            organizer_id BIGINT UNSIGNED NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            remaining_amount DECIMAL(10,2) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // 4. Table des réservations
        $pdo->exec("CREATE TABLE IF NOT EXISTS {$prefix}reservations (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            event_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED DEFAULT NULL,
            guest_name VARCHAR(191) DEFAULT NULL,
            guest_firstname VARCHAR(191) DEFAULT NULL,
            guest_postcode VARCHAR(10) DEFAULT NULL,
            guest_email VARCHAR(191) DEFAULT NULL,
            places INT UNSIGNED NOT NULL,
            amount_paid DECIMAL(10,2) DEFAULT 0,
            status ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        try {
            $pdo->exec("ALTER TABLE {$prefix}reservations
        ADD COLUMN IF NOT EXISTS guest_name VARCHAR(191) DEFAULT NULL,
        ADD COLUMN IF NOT EXISTS guest_firstname VARCHAR(191) DEFAULT NULL,
        ADD COLUMN IF NOT EXISTS guest_postcode VARCHAR(10) DEFAULT NULL");
        } catch (\PDOException $e) {
            // Ignore erreur si colonnes existent déjà ou si la syntaxe IF NOT EXISTS n'est pas supportée
        }
    }

    // Méthode exécutée à la désactivation du plugin
    public static function deactivate()
    {
        // On ne supprime pas les tables pour respecter la politique de conservation des données
    }
}
