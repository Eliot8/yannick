<?php

namespace App\Controller;

use App\Controller\Services\Helpers;
use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\ModeleChaussure;
use App\Entity\RetourneProduit;
use App\Form\RetourneType;
use App\Repository\CommandeRepository;
use App\Repository\PromotionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class RetourneProduitController extends AbstractController
{
    /**
     * Add retourne produit pour client 
     * @Route("/retourne/{taille}/{commande}", name="retourne_add")
     */
    public function retourne($taille, $commande, Helpers $helpers, Request $request, Security $security, EntityManagerInterface $entityManager)
    {
        // recuperer la commande 
        $commande = $this->getDoctrine()->getRepository(Commande::class)->findOneBy(['id' => $commande, 'client' => $security->getUser()]);
        // recuperer la ligne commande avec taille et commande 
        $ligne_commande = $this->getDoctrine()->getRepository(LigneCommande::class)->findOneBy(['taille' => $taille, 'commande' => $commande]);
        $form = $this->createForm(RetourneType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // ajouter le produit retourné a la table
            $retourne = new RetourneProduit();
            $retourne->setClient($security->getUser());
            $retourne->setCommande($commande);
            $retourne->setProduit($ligne_commande->getModeleChaussure());
            $retourne->setTaille($ligne_commande->getTaille());
            $retourne->setRaison($request->request->get('retourne')['raison']);
            $retourne->setDateRetourne(new \DateTime(date('Y-m-d')));
            $entityManager->persist($retourne);

            // definir que ligne commande est reclamée 
            // par default il prends la valeur 0 : n'est pa reclamée
            $ligne_commande->setReclame(1); // 1 c'est a dire reclame
            $entityManager->persist($retourne);
            $entityManager->flush();

            $this->addFlash('success', 'Votre reclamations a été envoyé a l\'administration.');
            return $this->redirectToRoute('commande_produit');
        }
        return $this->render('retourne/index.html.twig', [
            'produit' => $ligne_commande,
            'form' => $form->createView(),
            'list' => $helpers->getList(),
            'carts' => $helpers->getProduct(),
        ]);
    }

    /**
     * Recupere les produits retournés pour ADMIN
     * @Route("/reclamations", name="reclamations")
     */
    public function getReclamations(Helpers $helpers, PromotionRepository $promotionRepository)
    {
        // recupre les reclamations
        $reclamations = $this->getDoctrine()->getRepository(RetourneProduit::class)->findBy(['confirme' => null], ['id' => 'desc']);
        return $this->render('admin/reclamations/index.html.twig', [
            'items' => $reclamations,
            'list'  => $helpers->getList(), // list des marques
            'promotions' => $promotionRepository->findAll()
        ]);
    }

    /**
     * Methode Show Reclamation
     * @Route("/reclamations/{commande}/{taille}/{id}", name="show_reclamation")
     */
    public function showReclamation($commande, $taille, $id, Helpers $helpers, PromotionRepository $promotionRepository)
    {
        // recupre commande
        $commande = $this->getDoctrine()->getRepository(Commande::class)->findOneBy(['id' => $commande]);
        // recupre lignecommande par commande et taille et le champ reclame doit etre true (reclamé)
        $ligne_commande = $this->getDoctrine()->getRepository(LigneCommande::class)->findOneBy(['commande' => $commande, 'taille' => $taille, 'reclame' => true]);
        // recupre lignecommande par id (paramatere au dessus)
        $retourn_info = $this->getDoctrine()->getRepository(RetourneProduit::class)->findOneBy(['id' => $id]);

        // dd($commande);
        return $this->render('admin/reclamations/show.html.twig', [
            'produit' => $ligne_commande, // info de produit
            'retourne_info' => $retourn_info, // info de reclamation (raison et la date)
            'list'  => $helpers->getList(), // list des marques
            'promotions' => $promotionRepository->findAll()

        ]);
    }

    /**
     * Methode Accepter reclamation de cote ADMIN
     * @Route("/reclamations/{id}/accepter", name="accepter_reclamation")
     */
    public function accepterReclamation($id, EntityManagerInterface $entityManager, \Swift_Mailer $mailer, Security $security)
    {
        // recupere le produit reclamé
        $retourn_info = $this->getDoctrine()->getRepository(RetourneProduit::class)->findOneBy(['id' => $id]);

        // definir que le produit a été accepter
        $retourn_info->setConfirme(1); // 1 c'est a dire true (accepter)
        $entityManager->persist($retourn_info);
        $entityManager->flush();

        /* pour envoye un message par email (just gmail) */
        $message = (new \Swift_Message('l\'administrateur a accepté votre réclamation')) // titre de message
            ->setFrom($security->getUser()->getEmail())  // email de admin ** IMPORTANT ** aller à .env pour plus informations.
            ->setTo($retourn_info->getClient()->getEmail()) // email de le client
            ->setBody('Félicitations, votre réclamation a été approuvée et le produit vous sera retourné avec le remboursement.');  // le message
        $mailer->send($message); // envoye le message

        $this->addFlash('success', 'Le message d\'accepte a été envoyé au client ' . $retourn_info->getClient()->getNom() . ' avec succès.');
        return $this->redirectToRoute('reclamations');
    }

    /**
     * Methode Refuser reclamation de cote ADMIN
     * @Route("/reclamations/{id}/refuser", name="refuser_reclamation")
     */
    public function refuserReclamation($id, EntityManagerInterface $entityManager, \Swift_Mailer $mailer, Security $security)
    {
        // recupere le produit reclamé
        $retourn_info = $this->getDoctrine()->getRepository(RetourneProduit::class)->findOneBy(['id' => $id]);

        // definir que le produit a été refuse
        $retourn_info->setConfirme(0); // 0 c'est a dire false ou refuser 
        $entityManager->persist($retourn_info);
        $entityManager->flush();

        /* pour envoye un message par email (just gmail) */
        $message = (new \Swift_Message('l\'administrateur a refusée votre réclamation')) // titre de message
            ->setFrom($security->getUser()->getEmail()) // email de admin ** IMPORTANT ** aller à .env pour plus informations.
            ->setTo($retourn_info->getClient()->getEmail()) // email de le client
            ->setBody('Malheureusement, votre réclamation a été refusée .'); // le message
        $mailer->send($message); // envoye le message

        $this->addFlash('success', 'Le message de refuse a été envoyé au client ' . $retourn_info->getClient()->getNom() . ' avec succès ');
        return $this->redirectToRoute('reclamations');
    }
}
