<?php

namespace Entity;

class Offre {

    private ?int $id_offre = null;

    public function __construct(
        private ?string $titre = null,
        private ?string $description = null,
        private ?string $categorie = null,
        private ?string $niveau = null,
        private ?string $statut = 'active',
        private ?\DateTime $date_limite = null,
        private ?int $id_u = null,
        private ?int $vues = 0
    ) {}

    // 🔹 Getters & Setters
    public function getIdOffre(): ?int {
        return $this->id_offre;
    }

    public function setIdOffre(?int $id): void {
        $this->id_offre = $id;
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

    public function getDateLimite(): ?\DateTime {
        return $this->date_limite;
    }

    public function getIdU(): ?int {
        return $this->id_u;
    }

    public function getVues(): ?int {
        return $this->vues;
    }

 
}