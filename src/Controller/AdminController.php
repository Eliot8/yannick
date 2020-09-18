<?php

namespace App\Controller;

use App\Controller\Services\Helpers;
use App\Entity\ModeleChaussure;
use App\Entity\Photo;
use App\Entity\Promotion;
use App\Form\ModeleChaussureType;
use App\Form\PromotionType;
use App\Repository\ClientRepository;
use App\Repository\CommentaireRepository;
use App\Repository\MarqueRepository;
use App\Repository\ModeleChaussureRepository;
use App\Repository\PhotoRepository;
use App\Repository\PromotionRepository;
use App\Repository\StockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AdminController extends AbstractController
{
    /**
     * @var ClientRepository
     */
    private $marqueRepository;
    private $modeleChaussure;
    private $helpers;
    private $stocks;
    private $promotions;
    private $photos;
    private $commentaires;
    private $clients;
    function __construct(MarqueRepository $marqueRepository, ClientRepository $clientRepository, Helpers $helpers, StockRepository $stockRepository, PromotionRepository $promotionRepository, ModeleChaussureRepository $modeleChaussureRepository, PhotoRepository $photoRepository, CommentaireRepository $commentaireRepository)
    {
        $this->marqueRepository = $marqueRepository;
        $this->clientRepository = $clientRepository;
        $this->helpers = $helpers;
        $this->stocks = $stockRepository;
        $this->promotions = $promotionRepository;
        $this->modeleChaussure = $modeleChaussureRepository;
        $this->photos = $photoRepository;
        $this->commentaires = $commentaireRepository;
        $this->clients = $clientRepository;
    }

    /**
     * @Route("/admin_panel", name="admin_panel")
     */
    public function index(Security $security)
    {
        return $this->render('admin/panel/index.html.twig');
    }

    /**
     * *****************************************************************
     * ********************** METHODES DE PROMOTION ********************
     * *****************************************************************
     */

    /**
     * @Route("/admin/chaussures{id}/delete",name="admin_chaussures_delete")
     *
     * @param ModeleChaussure $chaussure
     * @param EntityManagerInterface $manager
     * @return RedirectResponse
     */

    public function  deleteShoes(ModeleChaussure $chaussure, EntityManagerInterface $manager)
    {
        if (count($chaussure->getCommandes()) > 0) {
            $this->addFlash(
                'warning',
                "you can't delete this shoe<stong>{$chaussure->getNom()}</stong>it has been already ordered!"
            );
        } else {
            $manager->remove($chaussure);
            $manager->flush();
            $this->addFlash(
                'success',
                "the shoe<strong>{$chaussure->getNom()}</strong>a bien été supprimé"
            );
        }
        return $this->redirectToRoute(' admin_modele_chaussure');
    }

    /**
     * METHODE PERMET DE NOUS REDIGEONS à LA PAGE DES PROMOTIONS ET AUSSI DE AJOUTER UNE NOUVELLE PROMOTION
     * @Route("/admin/promotions", name="admin_promotions")
     *
     * @return void
     */
    public function adminPromotion(Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(PromotionType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $promotion = new Promotion();
            $promotion->setDateDebut($form->getData()->getDateDebut());
            $promotion->setDateFin($form->getData()->getDateFin());
            $promotion->setPourcentage($form->getData()->getPourcentage());
            $promotion->setModeleChaussure($form->getData()->getModeleChaussure());
            $em->persist($promotion);
            $em->flush();
            $this->addFlash("success", "la promotion a été Ajoutée avec succès !");
            return new RedirectResponse($request->headers->get('referer'));
        }

        return $this->render('admin/promotion/index.html.twig', [
            'modeleChaussures' => $this->modeleChaussure->findAll(),
            'promotions' => $this->promotions->findAll(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * METHODE PERMET DE FAIRE MISE A JOUR LA PROMOTION
     * @Route("/admin/promotions/{id}/edit", name="admin_edit_promotion")
     *
     * @param [type] $id
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return void
     */
    public function adminEditPromotion($id, Request $request, EntityManagerInterface $em)
    {
        // cree le formulaire
        $form = $this->createForm(PromotionType::class, $this->promotions->findOneBy(['id' => $id]));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // recupere la promotion a modifier
            $promotion = $this->promotions->findOneBy(['id' => $id]);

            // set les valeurs et save
            $promotion->setDateDebut($form->getData()->getDateDebut());
            $promotion->setDateFin($form->getData()->getDateFin());
            $promotion->setPourcentage($form->getData()->getPourcentage());
            $em->persist($promotion);
            $em->flush();

            $this->addFlash("success", "la promotion a été modifiée avec succès !");
            return $this->redirectToRoute('admin_add_promotion');
        }
        return $this->render('admin/promotion/edit.html.twig', [
            'form' => $form->createView(),
            'promotion' => $this->promotions->findOneBy(['id' => $id])
        ]);
    }

    /**
     * METHODE PERMET DE SUPPRIMER UNE PROMOTION 
     * @Route("/admin/promotions/{id}/delete", name="admin_delete_promotion")
     *
     * @param [type] $id
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return void
     */
    public function adminDeletePromotion($id, Request $request, EntityManagerInterface $em)
    {
        $em->remove($this->promotions->findOneBy(['id' => $id]));
        $em->flush();
        $this->addFlash('success', 'la promotion a été supprimée avec succès !');
        return new RedirectResponse($request->headers->get("referer"));
    }


    /**
     * *****************************************************************
     * ***************** METHODES DE MODELE CHAUSSURE ******************
     * *****************************************************************
     */

    /**
     * @Route("/admin/modeleChaussure", name="admin_modele_chaussure")
     * @param ModeleChaussureRepository $repo
     */
    public function adminModeleChaussure(Helpers $helpers)
    {
        // on instancie un variable de type datetime 
        $date = new \DateTime();
        return $this->render('admin/admin_modele_chaussure/index.html.twig', [
            'modeleChaussures' => $this->modeleChaussure->findAll(),
            'list' => $this->marqueRepository->findAll(),
            'carts' => $this->helpers->getProduct(),
            'stocks' => $this->stocks->findAll(),
            'commandes' => $helpers->getCountcommandes(),
            'promotions' => $this->promotions->findByDate($date) // je l'avais crée cette methode findByDate dans le repository 
        ]);
    }

    /**
     * METHODE PERMET DE FAIRE MISE A JOUR MODELE CHAUSSURE
     * @Route("/admin/modeleChaussure/{id}/edit", name="admin_edit_modeleChaussure")
     *
     * @param [type] $id
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return void
     */
    public function adminEditModeleChaussure($id, Request $request, EntityManagerInterface $em)
    {
        // cree le formulaire
        $form = $this->createForm(ModeleChaussureType::class, $this->modeleChaussure->findOneBy(['id' => $id]));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // recupere la les info de modele chaussure a modifier
            $modeleChaussure = $this->modeleChaussure->findOneBy(['id' => $id]);

            // set les valeurs et save
            $modeleChaussure->setNom($form->getData()->getNom());
            $modeleChaussure->setMarque($form->getData()->getMarque());
            $modeleChaussure->setPrix($form->getData()->getPrix());
            $modeleChaussure->setDescription($form->getData()->getDescription());

            // ENREGISTER COVER IMAGE 
            $cover_image = $request->files->get('cover_image');
            if ($cover_image) {
                $filename = md5(uniqid()) . '.' . $cover_image->guessClientExtension();
                try {
                    $cover_image->move($this->getParameter('coverImage_directory'), $filename);
                    $modeleChaussure->setCoverImage($filename);
                } catch (\Throwable $th) {
                    $this->addFlash('danger', $th);
                }
            }


            // si l admin a supprimé des photo du multiple images
            if (count($request->request) >= 2) {
                // request contains un colone de modele chaussure et autre de images qui etais supprimer
                // alors pour chaque index de cette request sauf modele chaussure 
                for ($i = 0; $i < count($request->request) - 1; $i++) {
                    $photo = $this->photos->findOneBy(['id' => $request->request->get('img' . $i)]);
                    $em->remove($photo);
                    $em->flush();
                }
            }
            // ENREGISTER MULTIPLE IMAGES 
            $multiple_image = $request->files->get('multiple_files');
            // si ladmin a upload image 
            if ($multiple_image) {
                // cette boucle c pour enregistrer chaque photo de multiple images
                foreach ($multiple_image as $file) {
                    $filename = md5(uniqid()) . '.' . $file->guessClientExtension();
                    try {
                        $file->move($this->getParameter('coverImage_directory'), $filename);
                        $photo = new Photo();
                        $photo->setModeleChaussure($modeleChaussure);
                        $photo->setUrl($filename);
                        $em->persist($photo);
                        $em->flush();
                    } catch (\Throwable $th) {
                        $this->addFlash('danger', $th);
                    }
                }
            }
            $em->persist($modeleChaussure);
            $em->flush();

            $this->addFlash("success", "les modifications a été enregistrer avec succès !");
            return $this->redirectToRoute('admin_modele_chaussure');
        }
        return $this->render('admin/admin_modele_chaussure/edit.html.twig', [
            'form' => $form->createView(),
            'modeleChaussure' => $this->modeleChaussure->findOneBy(['id' => $id])
        ]);
    }
    /**
     * * METHODE PERMET D'AJOUTER UNE NOUVELLE MODELE CHAUSSURE
     * @Route("/admin/modeleChaussure/add", name="admin_add_modeleChaussure")
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return void
     */
    public function adminAddModeleChaussure(Request $request, EntityManagerInterface $em)
    {
        // cree le formulaire
        $form = $this->createForm(ModeleChaussureType::class);
        $form->handleRequest($request);

        /* pour $form->isValid() ne marche pas dans cette cas 
         je sais pas pourquoi mais l'ajoute fonctionne bien */
        if ($form->isSubmitted()) {

            // recupere la les info de modele chaussure a modifier
            $modeleChaussure = new ModeleChaussure();

            // set les valeurs et save
            $modeleChaussure->setNom($form->getData()->getNom());
            $modeleChaussure->setMarque($form->getData()->getMarque());
            $modeleChaussure->setPrix($form->getData()->getPrix());
            $modeleChaussure->setDescription($form->getData()->getDescription());

            // ENREGISTER COVER IMAGE 
            $cover_image = $request->files->get('cover_image');
            if ($cover_image) {
                $filename = md5(uniqid()) . '.' . $cover_image->guessClientExtension();
                    $cover_image->move($this->getParameter('coverImage_directory'), $filename);
                    $modeleChaussure->setCoverImage($filename);
            }

            // ENREGISTER MULTIPLE IMAGES 
            $multiple_image = $request->files->get('multiple_files');
            // si ladmin a upload image 
            if ($multiple_image) {
                // cette boucle c pour enregistrer chaque photo de multiple images
                foreach ($multiple_image as $file) {
                    $filename = md5(uniqid()) . '.' . $file->guessClientExtension();
                        $file->move($this->getParameter('coverImage_directory'), $filename);
                        $photo = new Photo();
                        $photo->setModeleChaussure($modeleChaussure);
                        $photo->setUrl($filename);
                        $em->persist($photo);
                        $em->flush();
                }
            }
            $em->persist($modeleChaussure);
            $em->flush();

            $this->addFlash("success", "Modele Chaussure a été ajouter avec succès !");
            return $this->redirectToRoute('admin_modele_chaussure');
        }

        return $this->render('admin/admin_modele_chaussure/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * METHODE PERMET DE SUPPRIMER UNE MODELE CHAUSSURE 
     * @Route("/admin/modeleChaussure/{id}/delete", name="admin_delete_modeleChaussure")
     *
     * @param [type] $id
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return void
     */
    public function adminDeleteModeleChaussure($id, Request $request, EntityManagerInterface $em)
    {
        $em->remove($this->modeleChaussure->findOneBy(['id' => $id]));
        $em->flush();
        $this->addFlash('success', 'Modele Chaussure a été supprimée avec succès !');
        return new RedirectResponse($request->headers->get("referer"));
    }

    /**
     * *****************************************************************
     * ******************* METHODES DE COMMENTAIRES ********************
     * *****************************************************************
     */

    /**
     * @Route("/admin/commentaires", name="admin_commentaires")
     *
     * @return void
     */
    public function adminCommentaires()
    {
        return $this->render('admin/commentaires/index.html.twig', [
            'commentaires' => $this->commentaires->findAll(),
            'list' => $this->marqueRepository->findAll(),
            'carts' => $this->helpers->getProduct(), // je l'avais crée cette methode findByDate dans le repository 
        ]);
    }

    /**
     * METHODE PERMET DE SUPPRIMER UN COMMENTAIRE
     * @Route("/admin/commentaire/{id}/delete", name="admin_delete_commentaire")
     *
     * @param [type] $id
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return void
     */
    public function adminDeleteCommentaire($id, Request $request, EntityManagerInterface $em)
    {
        $em->remove($this->commentaires->findOneBy(['id' => $id]));
        $em->flush();
        $this->addFlash('success', 'Le Commentaire a été supprimée avec succès !');
        return new RedirectResponse($request->headers->get("referer"));
    }

    /**
     * *****************************************************************
     * ********************** METHODES DE CLIENTS **********************
     * *****************************************************************
     */

    /**
     * @Route("/admin/clients", name="admin_clients")
     *
     * @return void
     */
    public function adminClients()
    {
        return $this->render('admin/clients/index.html.twig', [
            'clients' => $this->clients->findAll(),
            'list' => $this->marqueRepository->findAll(),
            'carts' => $this->helpers->getProduct(), // je l'avais crée cette methode findByDate dans le repository 
        ]);
    }

    /**
     * METHODE PERMET DE SUPPRIMER UN CLIENT
     * @Route("/admin/client/{id}/delete", name="admin_delete_client")
     *
     * @param [type] $id
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return void
     */
    public function adminDeleteClient($id, Request $request, EntityManagerInterface $em)
    {
        $em->remove($this->clients->findOneBy(['id' => $id]));
        $em->flush();
        $this->addFlash('success', 'Le Client a été supprimée avec succès !');
        return new RedirectResponse($request->headers->get("referer"));
    }

    /**
     * *****************************************************************
     * *********************** METHODES DE STOCKS **********************
     * *****************************************************************
     */

    /**
     * @Route("/admin/stocks", name="admin_stocks")
     *
     * @return void
     */
    public function adminStocks()
    {
        return $this->render('admin/stock/index.html.twig', [
            'stocks' => $this->stocks->findAll(),
            'list' => $this->marqueRepository->findAll(),
            'carts' => $this->helpers->getProduct(), // je l'avais crée cette methode findByDate dans le repository 
        ]);
    }

    /**
     * METHODE PERMET DE SUPPRIMER UN STOCK
     * @Route("/admin/stock/{id}/delete", name="admin_delete_stock")
     *
     * @param [type] $id
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return void
     */
    public function adminDeleteStock($id, Request $request, EntityManagerInterface $em)
    {
        $em->remove($this->stocks->findOneBy(['id' => $id]));
        $em->flush();
        $this->addFlash('success', 'Le Stock a été supprimée avec succès !');
        return new RedirectResponse($request->headers->get("referer"));
    }
}
