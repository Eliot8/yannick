<?php

namespace App\Entity;

use App\Repository\LigneCommandeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LigneCommandeRepository::class)
 */
class LigneCommande
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantite;

    /**
     * @ORM\Column(type="float")
     */
    private $prix;

    /**
     * @ORM\ManyToOne(targetEntity=ModeleChaussure::class, inversedBy="taille")
     */
    private $modeleChaussure;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $relation;

    /**
     * @ORM\ManyToOne(targetEntity=Taille::class, inversedBy="ligneCommandes")
     */
    private $taille;

    /**
     * @ORM\ManyToOne(targetEntity=Commande::class, inversedBy="ligneCommandes")
     */
    private $commande;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default": 0})
     */
    private $reclame;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function getModeleChaussure(): ?ModeleChaussure
    {
        return $this->modeleChaussure;
    }

    public function setModeleChaussure(?ModeleChaussure $modeleChaussure): self
    {
        $this->modeleChaussure = $modeleChaussure;

        return $this;
    }

    public function getRelation(): ?string
    {
        return $this->relation;
    }

    public function setRelation(?string $relation): self
    {
        $this->relation = $relation;

        return $this;
    }

    public function getTaille(): ?Taille
    {
        return $this->taille;
    }

    public function setTaille(?Taille $taille): self
    {
        $this->taille = $taille;

        return $this;
    }

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): self
    {
        $this->commande = $commande;

        return $this;
    }

    public function getReclame(): ?bool
    {
        return $this->reclame;
    }

    public function setReclame(bool $reclame): self
    {
        $this->reclame = $reclame;

        return $this;
    }
}
