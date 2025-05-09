<?php

namespace Adess\EventManager\Admin\ListTable;

// Assure que la classe de base WP_List_Table est chargée (fourni par WordPress)
if (! class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

use WP_List_Table;
use Adess\EventManager\Repositories\EventRepository;
use Adess\EventManager\Models\Event;

// Classe pour afficher le tableau des événements en back-office
class EventTable extends WP_List_Table
{
    // Référence au dépôt de données pour charger/enregistrer les événements
    private $repository;

    // Initialise la table (singulier, pluriel, désactive AJAX)
    public function __construct()
    {
        parent::__construct([
            'singular' => 'event',
            'plural'   => 'events',
            'ajax'     => false,
        ]);

        // Crée une instance du repository pour interagir avec la base
        $this->repository = new EventRepository();
    }

    // Définit les colonnes visibles dans le tableau
    public function get_columns(): array
    {
        return [
            'cb'                => '<input type="checkbox" />', // case à cocher pour actions groupées
            'id'                => 'ID',                         // Identifiant de l'événement
            'title'             => 'Événement',                 // Titre
            'location'          => 'Lieu',                      // Lieu
            'start_date'        => 'Date de début',             // Date de début
            'participant_count' => 'Participants',              // Nombre de participants
            'estimated_cost'    => 'Coût estimé',               // Coût calculé
            'status'            => 'Statut',                    // Statut (pending, validated...)
            'subsidy_amount'        => 'Subvention',

            'created_at'        => 'Créé le',                   // Date de création
        ];
    }

    // Colonnes sur lesquelles on peut trier
    protected function get_sortable_columns(): array
    {
        return [
            'id'         => ['id', true],          // Trie par ID par défaut
            'start_date' => ['start_date', false], // Trie par date de début
            'created_at' => ['created_at', false], // Trie par date de création
        ];
    }

    // Prépare les données : en-têtes, pagination et chargement
    public function prepare_items(): void
    {
        $columns  = $this->get_columns();
        $hidden   = [];
        $sortable = $this->get_sortable_columns();

        // Définit les en-têtes de colonnes
        $this->_column_headers = [$columns, $hidden, $sortable];

        // Paramètres de pagination
        $per_page     = 20;
        $current_page = $this->get_pagenum();
        $total_items  = $this->repository->countAll();

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ]);

        // Récupère la page courante d'éléments
        $this->items = $this->repository->findByPage(
            $per_page,
            ($current_page - 1) * $per_page,
            $this->get_sortable_column_order()
        );
    }

    // Affiche le contenu générique d'une colonne
    protected function column_default($item, $column_name)
    {
        // Détermine le getter à appeler (ex: getTitle pour "title")
        $method = 'get' . str_replace('_', '', ucwords($column_name, '_'));
        if (method_exists($item, $method)) {
            $value = $item->{$method}();
            // Si c'est une date, formate-la
            if ($value instanceof \DateTime) {
                return esc_html($value->format('Y-m-d H:i'));
            }
            // Sinon, retourne la valeur textuelle
            return esc_html((string) $value);
        }
        // Si pas de getter, debug
        return print_r($item, true);
    }

    // Case à cocher de la première colonne
    protected function column_cb($item): string
    {
        return sprintf(
            '<input type="checkbox" name="event[]" value="%d" />',
            $item->getId()
        );
    }

    // Colonne titre avec actions (éditer, supprimer)
    protected function column_title($item): string
    {
        // Liens d'action posés sous le titre
        $actions = [
            'edit'   => sprintf(
                '<a href="?page=adess_event_edit&event=%d">Éditer</a>',
                $item->getId()
            ),
            'delete' => sprintf(
                '<a href="?page=adess_event_list&action=delete&event=%d">Supprimer</a>',
                $item->getId()
            ),
        ];

        // Affiche le titre + les actions
        return sprintf('%1$s %2$s', esc_html($item->getTitle()), $this->row_actions($actions));
    }

    // Lit l'ordre et la direction du tri dans la requête HTTP
    private function get_sortable_column_order(): array
    {
        $orderby = ! empty($_REQUEST['orderby']) ? sanitize_text_field($_REQUEST['orderby']) : 'id';
        $order   = ! empty($_REQUEST['order'])   ? sanitize_text_field($_REQUEST['order'])   : 'ASC';
        return [$orderby, $order];
    }

   // Affiche le montant de la subvention
protected function column_subsidy_amount($item)
{
    // $item est une instance de Adess\EventManager\Models\Event
    $amount = $item->getSubsidyAmount();
    if ($amount <= 0) {
        return '—';
    }
    // format 2 décimales, virgule caractère français
    return number_format($amount, 2, ',', ' ') . ' €';
}


    // Enveloppe l'affichage dans un <form> pour les bulk actions
    public function display(): void
    {
        echo '<form method="post">';
        parent::display();
        echo '</form>';
    }
}
