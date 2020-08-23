<?php

namespace App\Controller;

use App\Controller\Services\Helpers;
use App\Repository\ClientRepository;
use App\Repository\MarqueRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
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
     * @Route("/", name="home")
     */
    public function index(Helpers $helpers)
    {
        $list = $this->marqueRepository->findAll();
        return $this->render('home/index.html.twig', [
            'list' => $list, // liste des marques
            'carts' => $helpers->getProduct(), // produits de panier
        ]);
    }
}
