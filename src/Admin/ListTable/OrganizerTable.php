<?php

namespace Adess\EventManager\Admin\ListTable;

use WP_List_Table;
use Adess\EventManager\Repositories\OrganizerRepository;
use Adess\EventManager\Models\Organizer;

// Assure la classe WP_List_Table est chargée (noyau WP)
if (! class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

// Classe pour afficher la liste des organisateurs dans le back-office
class OrganizerTable extends WP_List_Table
{
    // Référence vers le dépôt de données
    private $repository;

    // Initialise le tableau avec ses attributs
    public function __construct()
    {
        parent::__construct([
            'singular' => 'organizer',
            'plural'   => 'organizers',
            'ajax'     => false,
        ]);

        // Instanciation du repository pour accéder aux données
        $this->repository = new OrganizerRepository();
    }

    // Définit les colonnes affichées
    public function get_columns(): array
    {
        return [
            'cb'            => '<input type="checkbox" />', // case à cocher pour actions groupées
            'id'            => 'ID',
            'name'          => 'Nom',
            'type'          => 'Type',
            'contact_email' => 'Email contact',
            'status'        => 'Statut',
            'created_at'    => 'Créé le',
            'lieu_prestation' => 'Lieu de prestation',

        ];
    }

    /**
     * Colonne « Lieu de prestation »
     */
    protected function column_lieu_prestation(Organizer $item): string
    {
        // Récupère dans l'ordre : n°+voie, CP, ville
        $street = $item->getSecondStreet();
        $cp     = $item->getSecondPostalCode();
        $city   = $item->getSecondCity();

        // Protège contre des valeurs nulles
        $parts = array_filter([$street, $cp, $city]);

        return esc_html(implode(' ', $parts));
    }


    // Colonnes pouvant être triées
    protected function get_sortable_columns(): array
    {
        return [
            'id'         => ['id', true],
            'name'       => ['name', false],
            'created_at' => ['created_at', false],
        ];
    }

    // Prépare les données : pagination, tri, récupération
    public function prepare_items(): void
    {
        $columns  = $this->get_columns();
        $hidden   = [];
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = [$columns, $hidden, $sortable];

        // Paramètres de pagination
        $per_page     = 20;
        $current_page = $this->get_pagenum();
        $total_items  = $this->repository->countAll();

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ]);

        // Chargement des organisateurs pour la page courante
        $this->items = $this->repository->findByPage(
            $per_page,
            ($current_page - 1) * $per_page
        );
    }

    // Gestion des colonnes génériques (fallback)
    protected function column_default($item, $column_name)
    {
        // Déduction du getter à appeler
        $method = 'get' . str_replace('_', '', ucwords($column_name, '_'));
        if (method_exists($item, $method)) {
            $value = $item->{$method}();
            // Formatage de la date si nécessaire
            if ($value instanceof \DateTime) {
                return esc_html($value->format('Y-m-d H:i'));
            }
            return esc_html((string) $value);
        }
        // Sinon, affichage brut pour debug
        return print_r($item, true);
    }

    // Case à cocher pour chaque ligne
    protected function column_cb($item): string
    {
        return sprintf(
            '<input type="checkbox" name="organizer[]" value="%d" />',
            $item->getId()
        );
    }

    // Personnalisation de la colonne "name" avec actions
    protected function column_name($item): string
    {
        // Liens d'édition et de suppression
        $actions = [
            'edit'   => sprintf('<a href="?page=adess_organizer_edit&organizer=%d">Éditer</a>', $item->getId()),
            'delete' => sprintf('<a href="?page=adess_organizer_list&action=delete&organizer=%d">Supprimer</a>', $item->getId()),
        ];

        // Nom + actions
        return sprintf('%1$s %2$s', esc_html($item->getName()), $this->row_actions($actions));
    }

    // Récupère l'ordre de tri depuis la requête HTTP
    private function get_sortable_column_order(): array
    {
        $orderby = ! empty($_REQUEST['orderby']) ? sanitize_text_field($_REQUEST['orderby']) : 'id';
        $order   = ! empty($_REQUEST['order'])   ? sanitize_text_field($_REQUEST['order'])   : 'ASC';
        return [$orderby, $order];
    }

    // Enveloppe la table dans un formulaire pour les actions groupées
    public function display(): void
    {
        echo '<form method="post">';
        parent::display();
        echo '</form>';
    }
}
