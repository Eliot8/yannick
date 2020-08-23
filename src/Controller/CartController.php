<?php

namespace App\Controller;

use App\Controller\Services\Helpers;
use App\Repository\ModeleChaussureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;


class CartController extends AbstractController
{

    /**
     *@Route("/cart", name="cart_index")
     */
    public function index(Helpers $helpers)
    {
        return $this->render('cart/cart.html.twig', [
            'list' => $helpers->getList(), // liste des marques
            'carts' => $helpers->getProduct(), // produits de panier
            'items' => $helpers->getProduct(), // liste des produits 
            'total' => $helpers->getTotal() // total
        ]);
    }
    /**
     * Methode qui permet d'ajouter les produits au panier
     *@Route("/cart/add/{id}", name="cart_add")
     */
    public function add($id, Request $request, SessionInterface $session)
    {
        // si user n'a pas choisi aucune taille
        if ($request->request->get('taille') == 0 || $request->request->get('taille') == null) {
            $this->addFlash('danger', 'please select a taille !.');
            return $this->redirect($request->headers->get('referer'));
        }

        // si user choisi grands qte (out of stock)
        if ($request->request->get('qte') == 0) {
            $this->addFlash('danger', 'the Quantite of taille ' . $request->request->get('taille') . ' is out of stock !.');
            return $this->redirect($request->headers->get('referer'));
        }

        // recupre les produits de  panier
        $cart = $session->get('cart', []);

        //  si le client a deja choisi une taille mais il choisit une nouvelle qte de la meme taille (taille rest meme ms qte update)
        if (!empty($cart[$id][$request->request->get('taille')])) {
            $cart[$id][$request->request->get('taille')]['qte'] = $request->request->get('qte');
        } else {
            // si non (nouvelle taille) 
            $cart[$id][$request->request->get('taille')] = [
                'qte' => $request->request->get('qte'),
            ];
        }

        // stock la session (panier)
        $session->set('cart', $cart);

        $this->addFlash('success', 'Item est bien Ajouté');
        return $this->redirectToRoute('cart_index');
    }

    /**
     * Methode qui permet de supprimmer les produits dans le panier
     * @Route("/cart/remove/{id}/{taille}", name="cart_delete", requirements={"name"=".+"})
     */
    public function remove($id, $taille, SessionInterface $session)
    {
        // recupere les produits de panier
        $cart = $session->get('cart', []);
        
        // si le panier a une produit avec plusieurs taille alors on supprime le spécifique taille 
        if (count($cart[$id]) > 1) {
            unset($cart[$id][$taille]);
        } elseif (count($cart[$id]) == 1) {
            // si le panier a une produit avec une taille alors on supprime le produit 
            unset($cart[$id]);
        }
        
        // stocke la session (panier)
        $session->set('cart', $cart);
        
        $this->addFlash('success', 'Item est bien supprimer');
        
        return $this->redirectToRoute('cart_index');
    }
    /**
     * Methode qui permet de mise a jourla quantite des produits dans le panier
     *@Route("/cart/edit/{id}/", name="cart_update", requirements={"name"=".+"})
     */
    public function edit($id, Request $request, SessionInterface $session)
    {
        // recupere les produits de panier
        $cart = $session->get('cart', []);

        //  si  il choisi qte = 0
        if ($request->request->get('qte') == 0) {
            $this->addFlash('danger', 'impossible de choisir 0 dans Quantite. Veuillez Choisir une bonne Quantite!.');
            return $this->redirect($request->headers->get('referer'));
        }
        //  si non on cherche dans la session le produit (qui a meme taille et on met le nouvelle quantite)
        $cart[$id][$request->request->get('taille')]['qte'] = $request->request->get('qte');

        // stocke la session (panier)
        $session->set('cart', $cart);
        
        $this->addFlash('success', 'Item est bien Modifié');
        return $this->redirect($request->headers->get('referer'));
    }
}
