<?php

namespace App\Controller;

use App\Controller\Services\Helpers;
use App\Entity\Client;
use App\Entity\Marque;
use App\Entity\ModeleChaussure;
use App\Repository\MarqueRepository;
use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MarqueController extends AbstractController
{
    /**
     * @var ClientRepository
     */
    private $marqueRepository;
    private $clientRepository;
    function __construct(MarqueRepository $marqueRepository, ClientRepository $clientRepository)
    {
        $this->marqueRepository = $marqueRepository;
        $this->clientRepository = $clientRepository;
    }

    /**
     * @Route("/marques", name="marque")
     */
    public function index(Helpers $helpers)
    {
        $list = $this->marqueRepository->findAll();
        return $this->render('marque/index.html.twig', [
            'list' => $list, // liste des marques
            'carts' => $helpers->getProduct(), // produits de panier
        ]);
    }

    /**
     * Methode qui permet d'afficher les marques
     * @Route("/marques/{id}", name="marques.details")
     */
    public function afficheChaussure(int $id, Helpers $helpers)
    {
        // recupere les marques
        $list = $this->marqueRepository->findAll();
        // recupere produits
        $marque = $this->marqueRepository->find($id);
        $chaussures = $marque->getModeleChaussures();

        return $this->render('marque/chaussures.html.twig', [
            'list' => $list, // liste des marques
            'carts' => $helpers->getProduct(), // produits de panier
            'chaussures' => $chaussures // produits
        ]);
    }
}
