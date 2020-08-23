<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TailleRepository")
 */
class Taille
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
    private $taille;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ModeleChaussure", inversedBy="tailles")
     */
    private $modeleChaussures;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Commande", mappedBy="tailles")
     */
    private $commandes;

    /**
     * @ORM\OneToOne(targetEntity=Stock::class, mappedBy="taille")
     */
    private $stock;

    /**
     * @ORM\OneToMany(targetEntity=LigneCommande::class, mappedBy="taille")
     */
    private $ligneCommandes;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RetourneProduit", mappedBy="taille")
     */
    private $retourne;

   
    
    public function __construct()
    {
        $this->modeleChaussures = new ArrayCollection();
        $this->commandes = new ArrayCollection();
        $this->stock = new ArrayCollection();
        $this->ligneCommandes = new ArrayCollection();
        $this->retourne = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTaille(): ?int
    {
        return $this->taille;
    }

    public function setTaille(int $taille): self
    {
        $this->taille = $taille;

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
            $modeleChaussure->addTaille($this);
        }

        return $this;
    }

    public function removeModeleChaussure(ModeleChaussure $modeleChaussure): self
    {
        if ($this->modeleChaussures->contains($modeleChaussure)) {
            $this->modeleChaussures->removeElement($modeleChaussure);
            $modeleChaussure->removeTaille($this);
        }

        return $this;
    }

    /**
     * @return Collection|Commande[]
     */
    public function getCommandes(): Collection
    {
        return $this->commandes;
    }

    public function addCommande(Commande $commande): self
    {
        if (!$this->commandes->contains($commande)) {
            $this->commandes[] = $commande;
            $commande->addTaille($this);
        }

        return $this;
    }

    public function removeCommande(Commande $commande): self
    {
        if ($this->commandes->contains($commande)) {
            $this->commandes->removeElement($commande);
            $commande->removeTaille($this);
        }

        return $this;
    }

    /**
     * @return Collection|Stock[]
     */
    public function getStock(): Collection
    {
        return $this->stock;
    }

    public function addStock(Stock $stock): self
    {
        if (!$this->stock->contains($stock)) {
            $this->stock[] = $stock;
            $stock->setTaille($this);
        }

        return $this;
    }

    public function removeStock(Stock $stock): self
    {
        if ($this->stock->contains($stock)) {
            $this->stock->removeElement($stock);
            // set the owning side to null (unless already changed)
            if ($stock->getTaille() === $this) {
                $stock->setTaille(null);
            }
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
            $ligneCommande->setTaille($this);
        }

        return $this;
    }

    public function removeLigneCommande(LigneCommande $ligneCommande): self
    {
        if ($this->ligneCommandes->contains($ligneCommande)) {
            $this->ligneCommandes->removeElement($ligneCommande);
            // set the owning side to null (unless already changed)
            if ($ligneCommande->getTaille() === $this) {
                $ligneCommande->setTaille(null);
            }
        }

        return $this;
    }

    public function setModeleChaussures(?ModeleChaussure $modeleChaussures): self
    {
        $this->modeleChaussures = $modeleChaussures;

        return $this;
    }

    public function setStock(?Stock $stock): self
    {
        $this->stock = $stock;

        // set (or unset) the owning side of the relation if necessary
        $newTaille = null === $stock ? null : $this;
        if ($stock->getTaille() !== $newTaille) {
            $stock->setTaille($newTaille);
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
            $retourne->setTaille($this);
        }

        return $this;
    }

    public function removeRetourne(RetourneProduit $retourne): self
    {
        if ($this->retourne->contains($retourne)) {
            $this->retourne->removeElement($retourne);
            // set the owning side to null (unless already changed)
            if ($retourne->getTaille() === $this) {
                $retourne->setTaille(null);
            }
        }

        return $this;
    }
}
