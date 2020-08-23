<?php

namespace App\Controller;

use App\Controller\Services\Helpers;
use App\Repository\ModeleChaussureRepository;
use App\Repository\PromotionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class CheckoutController extends AbstractController
{
    /**
     *@Route("/checkout", name="checkout_index")
     */
    public function index(Helpers $helpers)
    {
        // le client n'a pas l'access de cette page (checkout) sauf que le panier n'est pas vide 
        if (count($helpers->getProduct()) == 0) {
            $this->addFlash('error', 'votre panier est totalemnt vide !.');
            return $this->redirectToRoute('cart_index');
        }
        return $this->render('checkout/index.html.twig', [
            'list' => $helpers->getList(), // liste des marque
            'carts' => $helpers->getProduct(), // produits de panier
            'items' => $helpers->getProduct(), // liste des produits
            'total' => $helpers->getTotal() // total
        ]);
    }

    /**
     * Methode qui permet de passer le payment avec stripe
     *@Route("/checkout/create", name="checkout_add")
     */
    public function add(Request $request, Helpers $helpers, Security $security)
    {
        // mise a jour le stock (qte diminue ...)
        $helpers->updateStock();

        // recupre les info des produit et client 
        // $content = new JsonResponse($helpers->getProductForStripe());

        // mettre ton apikey de strie ** IMPORTANT ** aller à checkout/index.html.twig et mettre l'autre key dans la partie javascript
        // \Stripe\Stripe::setApiKey('sk_test_51H5enQGbE9mY9SFDbZZpdg2DdhlzxXpwpKa1qAph1kkeRldKhWSKimlSIswoHim8N5WPAPhAH4YdriMF2ZbGHYBg00I00bLjhq');

        // \Stripe\Charge::create([
        //     'amount' => $helpers->getTotal() * 100, // total
        //     'currency' => 'eur', // divse
        //     'description' => 'Order',
        //     'source' => $request->request->get('stripeToken'), // token
        //     'receipt_email' => $security->getUser()->getEmail(), // email de client
        //     'metadata' => [
        //         'content' => $content // info de payement
        //     ]
        // ]);

        // ADD livraison  
        $helpers->addLivraison();

        // ADD COMMANDE 
        $helpers->addCommande();

        // ADD lignecommande 
        $helpers->addLigneCommande();
        
        $this->addFlash('success', 'Your payment has been successfully accepted !');
        return $this->redirectToRoute('thankyou');
    }
}
