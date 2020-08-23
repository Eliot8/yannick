<?php

namespace App\Entity;

use App\Repository\RetourneProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use App\entity\ModeleChaussure;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RetourneProduitRepository::class)
 */
class RetourneProduit
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Client", inversedBy="retourne")
     */
    private $client;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Commande", inversedBy="retourne")
     */
    private $commande;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ModeleChaussure", inversedBy="retourne")
     */
    private $produit;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Taille", inversedBy="retourne")
     */
    private $taille;

    /**
     * @ORM\Column(type="text")
     */
    private $raison;

    /**
     * @ORM\Column(type="date")
     */
    private $dateRetourne;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $confirme;

    public function __construct()
    {
        $this->produit = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRaison(): ?string
    {
        return $this->raison;
    }

    public function setRaison(string $raison): self
    {
        $this->raison = $raison;

        return $this;
    }

    public function getDateRetourne(): ?\DateTimeInterface
    {
        return $this->dateRetourne;
    }

    public function setDateRetourne(\DateTimeInterface $dateRetourne): self
    {
        $this->dateRetourne = $dateRetourne;

        return $this;
    }

    public function getConfirme(): ?bool
    {
        return $this->confirme;
    }

    public function setConfirme(?bool $confirme): self
    {
        $this->confirme = $confirme;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

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

    public function getProduit(): ?ModeleChaussure
    {
        return $this->produit;
    }

    public function setProduit(?ModeleChaussure $produit): self
    {
        $this->produit = $produit;

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
}
