<?php

namespace Adess\EventManager\Models;

// Classe représentant un organisateur (entreprise ou collectivité)
class Organizer
{
    // Identifiant unique en base de données
    private $id;

    // ID de l'utilisateur WordPress lié à cet organisateur
    private $user_id;

    // Type d'organisateur : 'company' ou 'collectivity'
    private $type;

    // Nom de l'entité
    private $name;

    // Adresse physique de l'entité
    private $address;

    // Nom du contact principal
    private $contactName;

    // Email du contact principal
    private $contactEmail;

    // Téléphone du contact principal
    private $phone;

    // Lieu de prestation par défaut pour cet organisateur

    private string $secondStreet;
    private string $secondPostalCode;
    private string $secondCity;

    // Statut de validation : 'pending', 'validated' ou 'rejected'
    private $status;

    // Date de création de l'enregistrement
    private $createdAt;

    // Constructeur avec tableau associatif pour hydrater l'objet
    public function __construct(array $data = [])
    {
        $this->id      = $data['id'] ?? null;
        $this->user_id = $data['user_id'] ?? 0;
        $this->type            = $data['type']            ?? '';
        $this->name            = $data['name']            ?? '';
        $this->address         = $data['address']         ?? '';
        $this->contactName     = $data['contact_name']     ?? null;
        $this->contactEmail    = $data['contact_email']    ?? null;
        $this->phone           = $data['phone']           ?? null;
        $this->secondStreet      = $data['second_street']      ?? '';
        $this->secondPostalCode  = $data['second_postal_code'] ?? '';
        $this->secondCity        = $data['second_city']        ?? '';
        $this->status          = $data['status']          ?? 'pending';

        if (isset($data['created_at'])) {
            $this->createdAt = new \DateTime($data['created_at']);
        } else {
            $this->createdAt = new \DateTime();
        }
    }

    // Getter pour ID
    public function getId(): ?int
    {
        return $this->id;
    }

    // Getter pour ID de l'utilisateur WordPress
    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    // Getter pour type
    public function getType(): string
    {
        return $this->type;
    }

    // Getter pour nom
    public function getName(): string
    {
        return $this->name;
    }

    // Getter pour adresse
    public function getAddress(): string
    {
        return $this->address;
    }

    // Getter pour nom du contact
    public function getContactName(): ?string
    {
        return $this->contactName;
    }

    // Getter pour email du contact
    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    // Getter pour téléphone
    public function getPhone(): ?string
    {
        return $this->phone;
    }


    // Getter pour statut de validation
    public function getStatus(): string
    {
        return $this->status;
    }

    // Getter pour la date de création
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getSecondStreet(): string
    {
        return $this->secondStreet;
    }

    public function getSecondPostalCode(): string
    {
        return $this->secondPostalCode;
    }

    public function getSecondCity(): string
    {
        return $this->secondCity;
    }

    public function getLieuPrestation(): string
    {
        return trim(sprintf(
            '%s %s %s',
            $this->secondStreet,
            $this->secondPostalCode,
            $this->secondCity
        ));
    }

    // Setters à ajouter si besoin de mettre à jour le modèle (optionnel)
}
