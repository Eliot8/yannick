<?php

namespace App\Controller;

use App\Controller\Services\Helpers;
use App\Entity\Stock;
use App\Entity\Taille;
use App\Entity\Livraison;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\ModeleChaussure;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;


class CommandeController extends AbstractController
{

    /**
     * @Route("/commande", name="commande")
     * @throws \Exception
     */
    public function index(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser() == null) {
            return $this->redirectToRoute("account_login");
        } else {
            // $session = $this->get('request_stack')->getCurrentRequest()->getSession();
            $session = $request->getSession();
            $commande = new Commande();
            if (!($session->has('cart'))) {
                return $this->redirectToRoute('panier_index');
                // return $this->redirectToRoute('cart_index');
            }

            $panier = $session->get('panier');
            $taille = $session->get('taille');


            $chaussures = $em->getRepository(ModeleChaussure::class)
                ->createQueryBuilder('c')
                ->where('c.id IN (:ids)')
                ->setParameter('ids', array_keys($session->get('panier')))
                ->getQuery()
                ->execute();
            $ligneCommande = [];
            $totalNet = 0;
            foreach ($chaussures as $chaussure) {

                $totalU = ($chaussure->getPrix() * $panier[$chaussure->getId()]);
                $totalNet += floatval($totalU);

                $ligneCommande[$chaussure->getId()] = [
                    'prixU' => $chaussure->getPrix(),
                    'quantite' => intval($panier[$chaussure->getId()]),
                    'totalU' => $totalU,
                ];

                $ligne = new LigneCommande();
                $ligne->setQuantite($panier[$chaussure->getId()]);
                //                $chaussure->setQuantite(($chaussure->getQuantite()) - ($panier[$chaussure->getId()]));
                $ligne->setModeleChaussure($chaussure);
                $ligne->setCommande($commande);
                $ligne->setPrix($totalU);

                if (isset($taille[$chaussure->getId()])) {
                    $tail = $em->getRepository(Taille::class)->find($taille[$chaussure->getId()]);
                    if ($tail) {
                        $ligne->setTaille($tail);
                        $stock = $em->getRepository(Stock::class)->findOneBy(array('modeleChaussure' => $chaussure, 'taille' => $tail));
                        if ($stock) {
                            $stock->setQuantite($stock->getQuantite() - $panier[$chaussure->getId()]);
                            $em->persist($stock);
                        }
                    }
                }
                $em->persist($ligne);
            }

            $user = $this->getUser();
            $commande->setMontantLigne($totalNet);
            $commande->setDateCommande(new \DateTime());
            $commande->setClient($this->getUser());
            $adress = $user->getAdresse();
            $ville = $user->getVille();
            $livraison = new Livraison();
            $livraison->setAdresse($adress);
            $livraison->setVille($ville);
            //  Convert the supplied date to timestamp
            //$fMin = strtotime($sStartDate);
            //$fMax = strtotime($sEndDate);

            // Generate a random number from the start and end dates
            //$fVal = mt_rand($fMin, $fMax);

            // Convert back to the specified date format
            //return date($sFormat, $fVal);

            $start_date = date('Y-m-d');
            $end_date = date("Y-m-d", strtotime("+7 day", strtotime($start_date)));
            $randomDate = $this->randomDate($start_date, $end_date);
            $livraison->setDateLivraison(new \DateTime($randomDate));

            $livraison->setDateLivraison(new \DateTime($randomDate));
            // $livraison = $this->getDoctrine()->getRepository('App\Entity\Livraison')->find($adress);

            $commande->setLivraison($livraison);
            $mode_paiement = $this->getDoctrine()->getRepository('App\Entity\ModePaiement')->find(1);
            $commande->setModePaiement($mode_paiement);

            $em->persist($commande);
            $em->flush();
            $session->remove('panier');
            //  $request->getSession()->getFlashBag()->add('success', 'Commande validée avec succès');
            $this->addFlash(
                'success',
                "Merci<strong class='text-danger'>{$user->getNom()}</strong> Commande validée avec succès"
            );

            return $this->redirectToRoute('home');
        }
    }
    function randomDate($sStartDate, $sEndDate, $sFormat = 'Y-m-d H:i:s')
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
