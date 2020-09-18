<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FactureRepository")
 */
class Facture
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $datePaiement;

    /**
     * @ORM\Column(type="date")
     */
    private $dateFacture;

    /**
     * @ORM\Column(type="date")
     */
    private $dateLimitePaiement;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $baseHTVA;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $montantTVA;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $totalHTVA;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $totalTTC;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Commande", inversedBy="facture", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $commande;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatePaiement(): ?\DateTimeInterface
    {
        return $this->datePaiement;
    }

    public function setDatePaiement(\DateTimeInterface $datePaiement): self
    {
        $this->datePaiement = $datePaiement;

        return $this;
    }

    public function getDateFacture(): ?\DateTimeInterface
    {
        return $this->dateFacture;
    }

    public function setDateFacture(\DateTimeInterface $dateFacture): self
    {
        $this->dateFacture = $dateFacture;

        return $this;
    }

    public function getDateLimitePaiement(): ?\DateTimeInterface
    {
        return $this->dateLimitePaiement;
    }

    public function setDateLimitePaiement(\DateTimeInterface $dateLimitePaiement): self
    {
        $this->dateLimitePaiement = $dateLimitePaiement;

        return $this;
    }

    public function getBaseHTVA(): ?string
    {
        return $this->baseHTVA;
    }

    public function setBaseHTVA(string $baseHTVA): self
    {
        $this->baseHTVA = $baseHTVA;

        return $this;
    }

    public function getMontantTVA(): ?string
    {
        return $this->montantTVA;
    }

    public function setMontantTVA(string $montantTVA): self
    {
        $this->montantTVA = $montantTVA;

        return $this;
    }

    public function getTotalHTVA(): ?string
    {
        return $this->totalHTVA;
    }

    public function setTotalHTVA(string $totalHTVA): self
    {
        $this->totalHTVA = $totalHTVA;

        return $this;
    }

    public function getTotalTTC(): ?string
    {
        return $this->totalTTC;
    }

    public function setTotalTTC(string $totalTTC): self
    {
        $this->totalTTC = $totalTTC;

        return $this;
    }

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(Commande $commande): self
    {
        $this->commande = $commande;

        return $this;
    }
}
