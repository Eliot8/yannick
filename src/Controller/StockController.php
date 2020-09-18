<?php

namespace App\Controller;

use App\Controller\Services\Helpers;
use App\Entity\ModeleChaussure;
use App\Entity\Stock;
use App\Form\StockType;
use App\Repository\ClientRepository;
use App\Repository\MarqueRepository;
use App\Repository\ModeleChaussureRepository;
use App\Repository\StockRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StockController extends AbstractController
{
    /**
     * @var ClientRepository
     */
    private $marqueRepository;
    private $stockRepository;
    private $chaussureRepository;
    private $manager;
    //private $clientRepository;
    function __construct(MarqueRepository $marqueRepository, StockRepository $stockRepository, ModeleChaussureRepository $chaussureRepository, EntityManagerInterface $manager)
    {
        $this->marqueRepository = $marqueRepository;
        //$this->clientRepository=$clientRepository;
        $this->stockRepository = $stockRepository;
        $this->chaussureRepository = $chaussureRepository;
        $this->manager = $manager;
    }


    /**
     * @Route("/stock", name="stock_add")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function createStock(Request $request, Helpers $helpers)
    {
        $stock = new Stock();
        $list = $this->marqueRepository->findAll();
        $form = $this->createForm(StockType::class, $stock);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // recupere data de request
            $data = $form->getData();
            
            // recupere le stock par data pour verifie si deja exist
            $stocks = $this->stockRepository->findBy(['modeleChaussure' => $data->getModeleChaussure(), 'taille' => $data->getTaille()]);
            
            // si le stock recupere est vide (n'exist pas)
            if (empty($stocks)) {
                $this->manager->persist($stock);
                $this->manager->flush();

                $this->addFlash('success', 'Stock Creé avec success');
                return $this->redirectToRoute('marque');

            // si le stock exist deja 
            } else {

                // si la quantite est 0 (faite mise a jour au niveau add stock)
                if ($data->getQuantite() == 0) {
                    $stocks[0]->setQuantite($data->getQuantite());
                    $this->manager->flush();
                    $this->addFlash('success', 'La quantite mis à jour avec succès.');
                    return $this->redirect($request->headers->get('referer'));

                // si la quantite est deffirent de 0 (faite mise a jour au niveau update stock)
                } else {
                    $this->addFlash('info', 'Veuillez faire mise a jour ici.');
                    return $this->redirectToRoute('stock_edit', [
                        'stock_id' => $stocks[0]->getId(),
                        'chaussure_slug' => $stocks[0]->getModeleChaussure()->getNom(),
                        'chaussure_id' => $stocks[0]->getModeleChaussure()->getId(),
                    ]);
                }
            }
        }

        return $this->render('stock/new.html.twig', [
            'form' => $form->createView(),
            'list' => $list,
            'carts' => $helpers->getProduct(),
        ]);
    }


    /**
     * @Route("/stock/{stock_id}/edit/{chaussure_slug}-{chaussure_id}" ,name="stock_edit",methods={"GET","POST"})
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return RedirectResponse|Response
     */
    public function edit($stock_id, Request $request, $chaussure_id, Helpers $helpers)
    {
        $list = $this->marqueRepository->findAll();
        $stock = $this->stockRepository->findOneBy(['id' => $stock_id]);
        $chaussure = $this->chaussureRepository->findOneBy(['id' => $chaussure_id]);

        
        $form = $this->createForm(StockType::class, $stock);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->persist($stock);
            $this->manager->flush();

            $this->addFlash('success', 'les modifications sont bien enregistré.');
            return $this->redirectToRoute('marque');
        }
        return $this->render('stock/edition.html.twig', [
            'form' => $form->createView(),
            'list' => $list,
            'carts' => $helpers->getProduct(),
            'chaussure' => $chaussure,
            'stock' => $stock
        ]);
    }
    /**
     * @Route("stock/edit", name="stock_editFromHeader")
     */
    public function editFromHeader(Request $request, Helpers $helpers)
    {
        $list = $this->marqueRepository->findAll();
        $stock = $this->stockRepository->findAll();
        $chaussure = $this->chaussureRepository->findAll();

        $form = $this->createForm(StockType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $stock = $this->stockRepository->findOneBy(['modeleChaussure' => $request->request->get('stock')['modeleChaussure'], 'taille' => $request->request->get('stock')['taille']]);
            $stock->setQuantite($request->request->get('stock')['quantite']);

            $this->addFlash('success', 'les modifications sont bien enregistré.');
            $this->manager->flush();
            return $this->redirectToRoute('marque');
        }
        return $this->render('stock/edition.html.twig', [
            'form' => $form->createView(),
            'list' => $list,
            'carts' => $helpers->getProduct(),
            'chaussure' => $chaussure,
            'stock' => $stock
        ]);
    }

    /**
     * Methode edit from admin 
     * @Route("/stock/{stock_id}/{chaussure_slug}/edit/{chaussure_id}" ,name="edit_from_admin",methods={"GET","POST"})
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return RedirectResponse|Response
     */
    public function editFromAdmin($stock_id, Request $request, $chaussure_id, Helpers $helpers)
    {
        $list = $this->marqueRepository->findAll();
        $stock = $this->stockRepository->findOneBy(['id' => $stock_id]);
        $chaussure = $this->chaussureRepository->findOneBy(['id' => $chaussure_id]);


        $form = $this->createForm(StockType::class, $stock);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->persist($stock);
            $this->manager->flush();

            $this->addFlash('success', 'les modifications sont bien enregistré.');
            return $this->redirectToRoute('admin_modele_chaussure');
        }
        return $this->render('stock/edition.html.twig', [
            'form' => $form->createView(),
            'list' => $list,
            'carts' => $helpers->getProduct(),
            'chaussure' => $chaussure,
            'stock' => $stock
        ]);
    }
}
