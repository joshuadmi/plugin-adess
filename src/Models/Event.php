<?php

namespace Adess\EventManager\Models;

// Modèle représentant un événement
class Event
{
    // Identifiant unique (NULL si nouvel objet)
    private $id;

    // Référence vers l'organisateur (clé étrangère)
    private $organizer_id;

    // Type de prestation (ex: formation, conférence)
    private $type;

    // Titre de l'événement
    private $title;

    // Lieu où se déroule l'événement
    private $location;

    // Date de début de l'événement
    private $start_date;


    // Nombre maximal de participants
    private $participant_count;

    // Coût estimé (peut être NULL avant calcul)
    private $estimated_cost;

    // Notes ou description complémentaire
    private $notes;

    // Statut de l'événement (pending, validated, cancelled)
    private $status;

    // Date de création en base (timestamp)
    private $created_at;

    // Hydratation de l'objet à partir d'un tableau de données
    public function __construct(array $data = [])
    {
        // On récupère chaque champ ou on met une valeur par défaut
        $this->id                = $data['id'] ?? null;
        $this->organizer_id      = $data['organizer_id'] ?? 0;
        $this->type              = $data['type'] ?? '';
        $this->title             = $data['title'] ?? '';
        $this->location          = $data['location'] ?? '';
        $this->start_date        = isset($data['start_date'])
            ? new \DateTime($data['start_date'])
            : new \DateTime();
        $this->participant_count = $data['participant_count'] ?? 0;
        $this->estimated_cost    = $data['estimated_cost'] ?? null;
        $this->notes             = $data['notes'] ?? null;
        $this->status            = $data['status'] ?? 'pending';
        $this->created_at        = isset($data['created_at'])
            ? new \DateTime($data['created_at'])
            : new \DateTime();
    }

    // GETTERS ET SETTERS

    // Récupérer l'ID
    public function getId(): ?int
    {
        return $this->id;
    }

    // Récupérer l'ID de l'organisateur
    public function getOrganizerId(): int
    {
        return $this->organizer_id;
    }

    // Modifier l'ID de l'organisateur
    public function setOrganizerId(int $id): self
    {
        $this->organizer_id = $id;
        return $this;
    }

    // Récupérer le type
    public function getType(): string
    {
        return $this->type;
    }

    // Modifier le type
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    // Récupérer le titre
    public function getTitle(): string
    {
        return $this->title;
    }

    // Modifier le titre
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    // Récupérer le lieu
    public function getLocation(): string
    {
        return $this->location;
    }

    // Modifier le lieu
    public function setLocation(string $location): self
    {
        $this->location = $location;
        return $this;
    }

    // Récupérer la date de début
    public function getStartDate(): \DateTime
    {
        return $this->start_date;
    }

    // Modifier la date de début
    public function setStartDate(\DateTime $date): self
    {
        $this->start_date = $date;
        return $this;
    }

    // Récupérer le nombre de participants
    public function getParticipantCount(): int
    {
        return $this->participant_count;
    }

    // Modifier le nombre de participants
    public function setParticipantCount(int $count): self
    {
        $this->participant_count = $count;
        return $this;
    }

    // Récupérer le coût estimé
    public function getEstimatedCost(): ?float
    {
        return $this->estimated_cost;
    }

    // Modifier le coût estimé
    public function setEstimatedCost(?float $cost): self
    {
        $this->estimated_cost = $cost;
        return $this;
    }

    // Récupérer les notes
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    // Modifier les notes
    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    // Récupérer le statut
    public function getStatus(): string
    {
        return $this->status;
    }

    // Modifier le statut
    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    // Récupérer la date de création
    public function getCreatedAt(): \DateTime
    {
        return $this->created_at;
    }
}
