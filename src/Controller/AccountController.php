<?php

namespace App\Controller;

use App\Controller\Services\Helpers;
use App\Entity\Client;
use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\PasswordUpdate;
use App\Entity\RetourneProduit;
use App\Form\AccountType;
use App\Form\PasswordUpdateType;
use App\Form\RegistrationType;
use App\Repository\ClientRepository;
use App\Repository\MarqueRepository;
use App\Repository\PromotionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AccountController extends AbstractController
{
    /**
     * @var ClientRepository
     */
    private $marqueRepository;
    private $clientRepository;
    private $promotion;
    function __construct(MarqueRepository $marqueRepository, ClientRepository $clientRepository, PromotionRepository $promotion)
    {
        $this->marqueRepository = $marqueRepository;
        $this->clientRepository = $clientRepository;
        $this->promotion = $promotion;
    }

    /**
     * @Route("/registration", name="account_registration")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordEncoderInterface $encoder
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function registration(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {
        $user = new Client();
        $list = $this->marqueRepository->findAll();
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $encoder->encodePassword($user, $user->getPassword());

            $user->setMotDePasse($hash);
            $manager->persist($user);
            $manager->flush();
            $this->addFlash(
                'success',
                "Bravo <strong class='text-danger'>{$user->getNom()}</strong> Inscription reussie"
            );

            return $this->redirectToRoute('account_login', ['list' => $list]);
        }

        return $this->render('account/registration.html.twig', [
            'form' => $form->createView(), 'list' => $list
        ]);
    }

    /**
     * @Route("/login", name="account_login")
     * @param AuthenticationUtils $utils
     * @return Response
     */
    public function login(AuthenticationUtils $utils)
    {

        $list = $this->marqueRepository->findAll();

        $error = $utils->getLastAuthenticationError();

        return $this->render('account/login.html.twig', ['hasError' => $error !== null, 'list' => $list]);
    }

    /**
     * @Route("/signout", name="account_logout")
     */
    public function logout()
    {
    }

    /**
     * @Route("/account/profile", name="account_profile")
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function profile(Request $request, EntityManagerInterface $manager)
    {
        $list = $this->marqueRepository->findAll();
        $user = $this->getUser();
        $form = $this->createForm(AccountType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                "Les données du profil ont été modifiées avec succès !"
            );
            return $this->redirectToroute('home');
        }
        return $this->render('account/profile.html.twig', [
            'form' => $form->createView(), 'list' => $list
        ]);
    }

    /**
     * @Route("/account/updatePassword", name="account_password")
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function updatePassword(Request $request, UserPasswordEncoderInterface $encoder, EntityManagerInterface $manager)
    {
        $list = $this->marqueRepository->findAll();
        $passwordUpdate = new PasswordUpdate();

        $user = $this->getUser();

        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!password_verify($passwordUpdate->getOldPassword(), $user->getPassword())) {
                $form->get('oldPassword')->adderror(new FormError("le mot de passe que vous avez tapé
              n'est pas votre de passe actuel!"));
            } else {
                $newPassword = $passwordUpdate->getNewPassword();
                $hash = $encoder->encodePassword($user, $newPassword);
                $user->setMotDePasse($hash);
                $manager->persist($user);
                $manager->flush();
                $this->addFlash(
                    'success',
                    "Votre mot de passe a bien été modifié!"
                );
                return $this->redirectToroute('home');
            }
        }

        return $this->render('account/password.html.twig', [
            'form' => $form->createView(),
            'list' => $list,
        ]);
    }



    /**
     * Récupère la liste de commandes de chaque client identifié
     * @Route("/myList", name="commande_produit")
     */
    public function index(Helpers $helpers, Security $security)
    {
        // Recupere les commande identifié
        $commandes = $this->getDoctrine()->getRepository(Commande::class)->findBy(['client' => $security->getUser()]);
        // Recupere les ligne commande identifié (par commande)
        $ligne_commande = $this->getDoctrine()->getRepository(LigneCommande::class)->findBy(['commande' => $commandes]);
        // Recupere les produits retournés identifié (par commande)
        $retourne_produit = $this->getDoctrine()->getRepository(RetourneProduit::class)->findBy(['commande' => $commandes]);

        

        return $this->render('commande_produit/index.html.twig', [
            'list' => $helpers->getList(), // list des marques
            'carts' => $helpers->getProduct(), // produits de panier
            'items' => $ligne_commande,
            'commandes' => $commandes,
            'retourne_produit' => $retourne_produit,
            'promotions' => $this->promotion->findAll()
        ]);
    }



    /**
     * la page de remerciment si le payment est reussie
     * @Route("/payment-accepter", name="thankyou")
     */
    public function thankYou(Helpers $helpers)
    {
        return $this->render('commande_produit/merci.html.twig', [
            'list' => $helpers->getList(), // list des marques
            'carts' => $helpers->getProduct(), // produits de panier
        ]);
    }
}
