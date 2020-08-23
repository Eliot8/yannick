<?php

namespace App\Entity;

use App\Repository\StockRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;

/**
 * @ORM\Entity(repositoryClass=StockRepository::class)
 */
class Stock
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
     * @ORM\ManyToOne(targetEntity=ModeleChaussure::class, inversedBy="stocks")
     */
    private $modeleChaussure;

    /**
     * @ORM\OneToOne(targetEntity=Taille::class, inversedBy="stock")
     */
    private $taille;

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

    public function getModeleChaussure(): ?ModeleChaussure
    {
        return $this->modeleChaussure;
    }

    public function setModeleChaussure(?ModeleChaussure $modeleChaussure): self
    {
        $this->modeleChaussure = $modeleChaussure;

        return $this;
    }

    public function getTaille()
    {
        return $this->taille;
    }

    public function setTaille( $taille)
    {
        $this->taille = $taille;

        return $this;
    }
}
