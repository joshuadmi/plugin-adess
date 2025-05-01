<?php
namespace Adess\EventManager; // pour éviter les conflits de noms

use Adess\EventManager\Admin\Menu;

// Classe principale du plugin
// Elle initialise le plugin et enregistre les hooks WordPress
class Plugin
{

    // Méthode d’exécution principale du plugin
    // Elle est appelée au chargement du plugin pour lancer les différentes fonctionnalités
    public function run()
    {
        // Enregistrement du menu dans le back-office uniquement
        // Cela évite de charger inutilement du code côté visiteur
        if (is_admin()) {
            $menu = new Menu();
            $menu->register();
        }


    }
}
