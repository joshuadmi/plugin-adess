<?php

namespace Adess\EventManager;

// Classe Installer : crée les tables nécessaires à l'activation du plugin
class Installer
{
    // Méthode exécutée à l'activation du plugin
    //Elle crée les quatre tables nécessaires (organizers, events, subsidies, reservations) en ouvrant d'abord une connexion PDO décorrélée de l'API $wpdb de WordPress.


    public static function activate()
    {
        // Connexion PDO manuelle pour s'affranchir du couplage avec WordPress
         // Récupération des constantes de configuration WordPress pour la base de données
        // DB_HOST, DB_NAME, DB_USER, DB_PASSWORD sont définies dans wp-config.php
        $host = DB_HOST;
        $db   = DB_NAME;
        $user = DB_USER;
        $pass = DB_PASSWORD;
        $charset = 'utf8mb4';

        // Construction du DSN (Data Source Name) pour PDO
    
        $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
        // boîte de réglages » que l’on passe à PDO pour lui dire comment réagir en cas d’erreur 

        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        // Création de l'objet PDO pour la connexion à la base de données
        $pdo = new \PDO($dsn, $user, $pass, $options);
        $prefix = 'wp_adess_';

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
            guest_email VARCHAR(191) DEFAULT NULL,
            places INT UNSIGNED NOT NULL,
            amount_paid DECIMAL(10,2) DEFAULT 0,
            status ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }

    // Méthode exécutée à la désactivation du plugin
    public static function deactivate()
    {
        // On ne supprime pas les tables pour respecter la politique de conservation des données
    }
}
