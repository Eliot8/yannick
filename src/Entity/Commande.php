<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommandeRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Commande
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $montantLigne;

    /**
     * @ORM\Column(type="date")
     */
    private $dateCommande;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Commentaire", mappedBy="commandes")
     */
    private $commentaires;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Client", inversedBy="commandes")
     * @ORM\JoinColumn(name="client_id", nullable=false)
     */
    private $client;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Livraison", cascade={"persist"},inversedBy="commandes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $livraison;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ModePaiement", inversedBy="commandes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $modePaiement;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\ModeleChaussure", mappedBy="commandes")
     */
    private $modeleChaussures;

    public function prePersist()
    {
        if (empty($this->dateCommande)) {
            $this->dateCommande = new \DateTime();
        }
    }

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Taille", inversedBy="commandes")
     */
    private $tailles;

    /**
     * @ORM\OneToMany(targetEntity=LigneCommande::class, mappedBy="commande")
     */
    private $ligneCommandes;


    // POUR LA TABLE RTOURNEPRODUIT
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RetourneProduit", mappedBy="commande")
     */
    private $retourne;
    public function __construct()
    {
        $this->commentaires = new ArrayCollection();
        $this->modeleChaussures = new ArrayCollection();
        $this->tailles = new ArrayCollection();
        $this->ligneCommandes = new ArrayCollection();
        $this->retourne = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMontantLigne(): ?string
    {
        return $this->montantLigne;
    }

    public function setMontantLigne(string $montantLigne): self
    {
        $this->montantLigne = $montantLigne;

        return $this;
    }

    public function getDateCommande(): ?\DateTimeInterface
    {
        return $this->dateCommande;
    }

    public function setDateCommande(\DateTimeInterface $dateCommande): self
    {
        $this->dateCommande = $dateCommande;

        return $this;
    }

    /**
     * @return Collection|Commentaire[]
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): self
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires[] = $commentaire;
            $commentaire->addCommande($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): self
    {
        if ($this->commentaires->contains($commentaire)) {
            $this->commentaires->removeElement($commentaire);
            $commentaire->removeCommande($this);
        }

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

    public function getLivraison(): ?Livraison
    {
        return $this->livraison;
    }

    public function setLivraison(?Livraison $livraison): self
    {
        $this->livraison = $livraison;

        return $this;
    }

    public function getModePaiement(): ?ModePaiement
    {
        return $this->modePaiement;
    }

    public function setModePaiement(?ModePaiement $modePaiement): self
    {
        $this->modePaiement = $modePaiement;

        return $this;
    }

    /**
     * @return Collection|ModeleChaussure[]
     */
    public function getModeleChaussures(): Collection
    {
        return $this->modeleChaussures;
    }

    public function addModeleChaussure(ModeleChaussure $modeleChaussure): self
    {
        if (!$this->modeleChaussures->contains($modeleChaussure)) {
            $this->modeleChaussures[] = $modeleChaussure;
            $modeleChaussure->addCommande($this);
        }

        return $this;
    }

    public function removeModeleChaussure(ModeleChaussure $modeleChaussure): self
    {
        if ($this->modeleChaussures->contains($modeleChaussure)) {
            $this->modeleChaussures->removeElement($modeleChaussure);
            $modeleChaussure->removeCommande($this);
        }

        return $this;
    }

    /**
     * @return Collection|Taille[]
     */
    public function getTailles(): Collection
    {
        return $this->tailles;
    }

    public function addTaille(Taille $taille): self
    {
        if (!$this->tailles->contains($taille)) {
            $this->tailles[] = $taille;
        }

        return $this;
    }

    public function removeTaille(Taille $taille): self
    {
        if ($this->tailles->contains($taille)) {
            $this->tailles->removeElement($taille);
        }

        return $this;
    }

    /**
     * @return Collection|LigneCommande[]
     */
    public function getLigneCommandes(): Collection
    {
        return $this->ligneCommandes;
    }

    public function addLigneCommande(LigneCommande $ligneCommande): self
    {
        if (!$this->ligneCommandes->contains($ligneCommande)) {
            $this->ligneCommandes[] = $ligneCommande;
            $ligneCommande->setCommande($this);
        }

        return $this;
    }

    public function removeLigneCommande(LigneCommande $ligneCommande): self
    {
        if ($this->ligneCommandes->contains($ligneCommande)) {
            $this->ligneCommandes->removeElement($ligneCommande);
            // set the owning side to null (unless already changed)
            if ($ligneCommande->getCommande() === $this) {
                $ligneCommande->setCommande(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|RetourneProduit[]
     */
    public function getRetourne(): Collection
    {
        return $this->retourne;
    }

    public function addRetourne(RetourneProduit $retourne): self
    {
        if (!$this->retourne->contains($retourne)) {
            $this->retourne[] = $retourne;
            $retourne->setCommande($this);
        }

        return $this;
    }

    public function removeRetourne(RetourneProduit $retourne): self
    {
        if ($this->retourne->contains($retourne)) {
            $this->retourne->removeElement($retourne);
            // set the owning side to null (unless already changed)
            if ($retourne->getCommande() === $this) {
                $retourne->setCommande(null);
            }
        }

        return $this;
    }
}
