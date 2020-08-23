<?php

namespace App\Controller\Services;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\Livraison;
use App\Repository\CommandeRepository;
use App\Repository\LivraisonRepository;
use App\Repository\MarqueRepository;
use App\Repository\ModeleChaussureRepository;
use App\Repository\ModePaiementRepository;
use App\Repository\PromotionRepository;
use App\Repository\StockRepository;
use App\Repository\TailleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;

class Helpers
{
    // cette class cest pour gere les methodes nécessaires et bien formee le code 
    private $list;
    private $shoe;
    private $stock;
    private $taille;
    private $commande;
    private $em;
    private $promotion;
    private $livraision;
    private $security;
    private $modePaiement;
    private $sessionInterface;
    public function __construct(SessionInterface $sessionInterface, MarqueRepository $marqueRepository, ModeleChaussureRepository $modeleChaussureRepository, StockRepository $stockRepository, TailleRepository $tailleRepository, EntityManagerInterface $em, PromotionRepository $promotionRepository, LivraisonRepository $livraisonRepository, ModePaiementRepository $modePaiementRepository, CommandeRepository $commandeRepository, Security $security)
    {
        $this->list = $marqueRepository;
        $this->shoe = $modeleChaussureRepository;
        $this->stock = $stockRepository;
        $this->taille = $tailleRepository;
        $this->em = $em;
        $this->promotion = $promotionRepository;
        $this->livraision = $livraisonRepository;
        $this->modePaiement = $modePaiementRepository;
        $this->security = $security;
        $this->sessionInterface = $sessionInterface;
        $this->commande = $commandeRepository;
    }

    // RECUPERE LA LISTE DES MARQUES
    public function getList()
    {
        return $this->list->findAll();
    }

    // RECUPERE LA LISTE DES PRODUCTS AU NIVEAU DE SESSION
    public function getProduct(): array
    {
        // recuper le panier
        $cart = $this->sessionInterface->get('cart', []);
        
        $items = [];

        /* pour chaque prdouit dans le panier
        *
        * cart s'affiche de cette maniere : 
        *                                   [id => [
        *                                       taille => [
        *                                       "qte" => "quantite
        *                                           ]
        *                                       ]
        *                                   ]
        */

        // le role de cette fonction est de recupere les produits avec plus d'informations
        foreach ($cart as $id => $taille) {
            foreach ($taille as $_taille => $qte) {
                $stock = $this->stock->findOneBy(['modeleChaussure' => $this->shoe->find($id)->getId(), 'taille' => $this->taille->findOneBy(['taille' => $_taille])->getId()]);
                $promo = 0;
                if ($this->getPromotion($this->shoe->find($id)) !== 0) {
                    $promo = $this->getPromotion($this->shoe->find($id));
                }

                // le tableau a return
                $items[] = [
                    'stock' => $stock,
                    'product' => $this->shoe->find($id),
                    'taille' => $_taille,
                    'qte' => $qte['qte'],
                    'promo' => $promo
                ];
            }
        }
        return $items;
    }

    // RECUPERE LA LISTE DES PRODUCTS AU NIVEAU DE SESSION (POUR STRIPE) CELA EST DIFFERENT  Au dessus
    public function getProductForStripe(): array
    {
        // recuper le panier
        $cart = $this->sessionInterface->get('cart', []);

        $items = [];
        // recupere les produits avec informations nécessaire pour stripe 
        foreach ($cart as $id => $taille) {
            foreach ($taille as $_taille => $qte) {
                $items[] = [
                    'product' => $this->shoe->find($id)->getNom(),
                    'taille' => $_taille,
                    'qte' => $qte['qte']
                ];
            }
        }
        return $items;
    }

    // RECUPERE LA LISTE DES PRODUCTS AU NIVEAU DE SESSION (POUR LIGNE COMMANDE) 
    public function getProductForLigneCommande(): array
    {
        // recuper le panier
        $cart = $this->sessionInterface->get('cart', []);
        $items = [];
        // recupere les produits avec informations nécessaire pour lignecommande 
        foreach ($cart as $id => $taille) {
            foreach ($taille as $_taille => $qte) {
                $items[] = [
                    'product' => $this->shoe->find($id),
                    'product_price' => $this->shoe->find($id)->getPrix(),
                    'taille' => $_taille,
                    'qte' => $qte['qte']
                ];
            }
        }
        return $items;
    }

    // RECUPERE LE TOTAL 
    public function getTotal()
    {
        $total = 0;
        // pour chaque produit
        foreach ($this->getProduct() as $item) {
            // verifie si le produit a promotion
            if ($this->getPromotion($item['product']) === 0) {
                // calcue sans promotion total
                $total +=  $item['product']->getPrix() * $item['qte'];
            } else {
                // calcule total avec promotion
                $total +=  $this->getPromotion($item['product']) * $item['qte'];
            }
        }
        return $total;
    }

    // METHODE UPDATE STOCK
    public function updateStock()
    {
        foreach ($this->getProduct() as $item) {
            // recuper la taille
            $taille = $this->taille->findBy(['taille' => $item['taille']]);

            // recuper le stock par taille
            $stock = $this->stock->findBy(['modeleChaussure' => $item['product']->getId(), 'taille' => $taille]);

            // recuper la qte
            $newQte = $stock[0]->getQuantite() - $item['qte'];

            // verifie si le stock exist deja
            if ($newQte > 0 || $newQte == 0) {
                $stock[0]->setQuantite($newQte);
                $this->em->persist($stock[0]);
                $this->em->flush();
            }
        }
    }

    // RECUPERER LA PROMOTION
    public function getPromotion(object $product): int
    {
        $newPrice = 0;

        // pour chaque promotion
        foreach ($this->promotion->findAll() as $promotion_item) {

            // verifie si le produit a une promotion
            if ($product->getId() === $promotion_item->getModeleChaussure()->getId()) {

                // si date fin de promotion est plus grand d'aujourd'hui
                if ($promotion_item->getDateFin()->format('Y-m-d') > date('Y-m-d')){
                    // calcule le nouveau prix
                    $newPrice = $product->getPrix() - ($product->getPrix() * ($promotion_item->getPourcentage() / 100));
                }
            }
        }
        return $newPrice;
    }

    // RECUPERER LE TOTAL TTC POUR LA FACTURE 
    public function getTotalTTC()
    {
        $totalTTC = 0;

        // pour chaque produit au niveau de panier 
        foreach ($this->getProduct($this->sessionInterface) as $item) {

            // verifier si le produit a des prromotions
            if ($this->getPromotion($item['product']) === 0) {

                // calcule sans promotion
                $totalTTC +=  ($item['product']->getPrix() * $item['qte']) * (1 + (20 / 100));
            } else {

                // calcule avec promotion
                $totalTTC +=  ($this->getPromotion($item['product']) * $item['qte']) * (1 + (20 / 100));
            }
        }
        return $totalTTC;
    }

    // RECUPERE LIVRAISION 
    public function getLivraison()
    {
        // recupere les laivrision en order decroisant
        $livr = $this->livraision->findOneBy([], ['id' => 'desc']);

        // si la table laivrision est vide 
        if (count((array)$livr) == 0) {
            return false;
        }
        return $livr;
    }

    // METHOD ADD LIVRAISION
    public function addLivraison(): void
    {
        // recuper user qui est logee
        $user = $this->security->getUser();

        // cree une nouvelle livraison
        $livraison = new Livraison();
        $livraison->setVille($user->getVille());
        $start_date = date('Y-m-d');
        $end_date = date("Y-m-d", strtotime("+7 day", strtotime($start_date)));
        $randomDate = $this->randomDate($start_date, $end_date);
        $livraison->setDateLivraison(new \DateTime($randomDate));
        $livraison->setAdresse($user->getAdresse());
        $this->em->persist($livraison);
        $this->em->flush();
    }

    // METHOD ADD COMMANDE
    public function addCommande(): void
    {
        // recuper user qui est logee
        $user = $this->security->getUser();
        // cree une nouvelle commande
        $commande = new Commande();
        $commande->setClient($user);
        $commande->setLivraison($this->livraision->findOneBy([], ['id' => 'desc']));
        $commande->setModePaiement($this->modePaiement->find(1));
        $commande->setMontantLigne($this->getTotal());
        $commande->setDateCommande(new \DateTime(date('Y-m-d')));
        $this->em->persist($commande);
        $this->em->flush();
    }

    // METHOD ADD LIGNE COMMANDE
    public function addLigneCommande()
    {
        // recupere les produit de ligne commande (produit du panier)
        $items = $this->getProductForLigneCommande();
        // si items est vide
        if (empty($items)) {
            return false;
        } else {
            foreach ($items as $item) {
                // ajouter chaque produit au ligne commande
                $ligneCommande = new LigneCommande();
                $ligneCommande->setModeleChaussure($item['product']);
                $ligneCommande->setTaille($this->taille->findOneBy(['taille' => $item['taille']]));
                $ligneCommande->setCommande($this->commande->findOneBy([], ['id' => 'desc']));
                $ligneCommande->setQuantite($item['qte']);
                $ligneCommande->setPrix($item['product_price']);
                $ligneCommande->setReclame(0);
                $this->em->persist($ligneCommande);
                $this->em->flush();
            }
        }
    }

    // METHOD RANDOM DATE (ton methode ahah)
    private function randomDate($sStartDate, $sEndDate, $sFormat = 'Y-m-d H:i:s')
    {
        // Convert the supplied date to timestamp
        $fMin = strtotime($sStartDate);
        $fMax = strtotime($sEndDate);

        // Generate a random number from the start and end dates
        $fVal = mt_rand($fMin, $fMax);

        // Convert back to the specified date format
        return date($sFormat, $fVal);
    }
}
