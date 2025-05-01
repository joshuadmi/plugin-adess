<?php

namespace Adess\EventManager\Admin\ListTable;

use WP_List_Table;
use Adess\EventManager\Repositories\SubsidyRepository;
use Adess\EventManager\Models\Subsidy;

// Classe SubsidyTable : affiche la liste des subventions en back-office
class SubsidyTable extends WP_List_Table
{
    // Repository pour accéder aux données des subventions
    private $repository;

    // Constructeur : définit les labels et initialise le repository
    public function __construct()
    {
        parent::__construct([
            'singular' => 'subsidy',   // nom singulier de l'objet
            'plural'   => 'subsidies',  // nom pluriel de l'objet
            'ajax'     => false,        // désactive AJAX
        ]);
        $this->repository = new SubsidyRepository();
    }

    // Définit les colonnes de la table (checkbox, ID, organisateur, montant total, montant restant, date)
    public function get_columns(): array
    {
        return [
            'cb'               => '<input type="checkbox" />',
            'id'               => 'ID',
            'organizer_id'     => 'Organisateur',
            'total_amount'     => 'Montant total',
            'remaining_amount' => 'Montant restant',
            'created_at'       => 'Créé le',
        ];
    }

    // Colonnes autorisées au tri
    protected function get_sortable_columns(): array
    {
        return [
            'id'               => ['id', true],
            'total_amount'     => ['total_amount', false],
            'remaining_amount' => ['remaining_amount', false],
            'created_at'       => ['created_at', false],
        ];
    }

    // Prépare les données : pagination, tri et chargement des items
    public function prepare_items(): void
    {
        $columns  = $this->get_columns();
        $hidden   = [];
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = [$columns, $hidden, $sortable];

        // Pagination : nombre d'items par page et numéro de page courante
        $per_page     = 20;
        $current_page = $this->get_pagenum();
        $total_items  = $this->repository->countAll();

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ]);

        // Récupération des subventions pour la page courante
        $this->items = $this->repository->findByPage(
            $per_page,
            ($current_page - 1) * $per_page,
            $this->get_sortable_column_order()
        );
    }

    // Affiche le contenu des colonnes par défaut
    protected function column_default($item, $column_name)
    {
        if (property_exists($item, $column_name)) {
            return esc_html((string) $item->{$column_name});
        }
        return print_r($item, true);
    }

    // Checkbox pour les bulk actions
    protected function column_cb($item): string
    {
        return sprintf(
            '<input type="checkbox" name="subsidy[]" value="%d" />',
            $item->getId()
        );
    }

    // Colonne ID avec liens Éditer / Supprimer
    protected function column_id($item): string
    {
        $actions = [
            'edit'   => sprintf('<a href="?page=adess_subsidy_edit&subsidy=%d">Éditer</a>', $item->getId()),
            'delete' => sprintf('<a href="?page=adess_subsidy_list&action=delete&subsidy=%d">Supprimer</a>', $item->getId()),
        ];
        return sprintf('%1$s %2$s', esc_html($item->getId()), $this->row_actions($actions));
    }

    // Récupère la colonne et l'ordre de tri depuis la requête HTTP
    private function get_sortable_column_order(): array
    {
        $orderby = !empty($_REQUEST['orderby']) ? sanitize_text_field(wp_unslash($_REQUEST['orderby'])) : 'id';
        $order   = !empty($_REQUEST['order'])   ? sanitize_text_field(wp_unslash($_REQUEST['order']))   : 'ASC';
        return [$orderby, $order];
    }

    // Affiche la table enveloppée dans un <form>
    public function display(): void
    {
        echo '<form method="post">';
        parent::display();
        echo '</form>';
    }
}
