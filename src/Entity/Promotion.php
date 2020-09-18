<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PromotionRepository")
 * @UniqueEntity(
 *     fields="modeleChaussure",
 *     message="la promotion de cette chaussure est deja exist"
 * )
 */
class Promotion
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     * @Assert\GreaterThan("today", message="la date debut doit etre superieur de la date d'aujourd'hui")
     */
    private $dateDebut;

    /**
     * @ORM\Column(type="date")
     * @Assert\GreaterThan("today")
     */
    private $dateFin;

    /**
     * @ORM\Column(type="float")
     * @Assert\Range(min=1, max=99, minMessage="Vous devez mesurer au moins 1 pour entrer", maxMessage="Vous ne pouvez pas mesurer plus de 99 pour entrer")
     */
    private $pourcentage;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ModeleChaussure", inversedBy="promotions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $modeleChaussure;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getPourcentage(): ?float
    {
        return $this->pourcentage;
    }

    public function setPourcentage(float $pourcentage): self
    {
        $this->pourcentage = $pourcentage;

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

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        if ($this->dateDebut > $this->dateFin) {
            $context->buildViolation('La date de début doit être antérieure à la date de fin')
                ->atPath('dateDebut')
                ->addViolation();
        }
    }
}
