<?php

namespace Entity;

class Demande {

    private ?int $id_demande = null;

    public function __construct(
        private ?string $titre = null,
        private ?string $description = null,
        private ?string $categorie = null,
        private ?string $niveau = null,
        private ?string $statut = 'active',
        private ?\DateTime $date_creation = null,
        private ?int $id_u = null
    ) {}

    // Getters & Setters

    public function getIdDemande(): ?int {
        return $this->id_demande;
    }

    public function setIdDemande(?int $id): void {
        $this->id_demande = $id;
    }

    public function getTitre(): ?string {
        return $this->titre;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function getCategorie(): ?string {
        return $this->categorie;
    }

    public function getNiveau(): ?string {
        return $this->niveau;
    }

    public function getStatut(): ?string {
        return $this->statut;
    }

    public function getDateCreation(): ?\DateTime {
        return $this->date_creation;
    }

    public function getIdU(): ?int {
        return $this->id_u;
    }

}