<?php

namespace App\Twig;

use App\Repository\LigneCommandeRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;


class AppExtension extends AbstractExtension {

    private $commandes;

    public function __construct(LigneCommandeRepository $ligneCommandeRepository)
    {
        $this->commandes = $ligneCommandeRepository;
    }

    public function getFunction()
    {
        return [
            new TwigFunction('getCommandes', [$this, 'getCommandes']),
        ];
    }
    /**
     * get count commandes by id shoe
     *
     * @param integer $idShoe
     * @return integer
     */
    public function getCommandes(int $idShoe): int 
    {
        return count($this->commandes->findBy(['modeleChaussure' => $idShoe]));
    }
}


