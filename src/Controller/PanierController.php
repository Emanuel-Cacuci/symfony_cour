<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Produit;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PanierController extends AbstractController
{
    #[Route('/panier', name: 'panier')]
    public function index(PanierRepository $repo): Response
    {

        // on recupére les paniers liés au user connecté
        $paniers = $repo->findBy(['user' => $this->getUser()]);
        // findBy() est une méthode de PanierRepository, cherche dans le panier toutes les lignes ou ma collone user correspond au user connecté
        // montre moi tous les paniers appartenant aux user

        dump($paniers);

        // foreach($paniers as $panier){


        // }

        return $this->render('panier/index.html.twig', [
            "paniers"=>$paniers
        ]);
    }

    #[Route('/panier/ajouter/{id}', name: 'panier_ajouter')]
    public function ajouter(Request $request, Produit $produit, EntityManagerInterface $em, PanierRepository $repo): Response
    {

        // Request  : represente les requetes HTTP (GET PUT DELETE)
        // Produit  : represente le produit que l'on veut ajouter au panier
        // EntityManagerInterface : represente la connexion  à la basse de données (UPDATE INSERT SELECT)
        // PanierRepository : permet de recupérer le panier de l'utilisateur connecté

        // dump($request->query);
        $user = $this->getUser(); // je recupére le user qui est connecté

        // on recupére la quantité demandé par le user via url (METHODE GET)
        // si aucune quantité n'es pas renseigné on va prendre 1 par default
        $quantite = max(1, $request->query->get('quantite', 1));

        // on cherche si une ligne de panier existe deja pour cet utilisateur et ce produit
        $ligne = $repo->findOneBy(['user' => $user, 'produit' => $produit]);

        // method findOneBy() prend un tableau en argument, c'est un tableau associatif, la clé est le nom de d'un champ ou d'une propriéte de l'entité
        // et la valeur est la valeur de ce champ ou de cette propriéte


        if ($ligne) {

            // si une ligne exciste deja dans le repository 
            // on ajoute la quantité demandé à quantité existante
            $ligne->setQuantity($ligne->getQuantity()+$quantite);
            $produit->setStock($produit->getStock()-$quantite);

        } else {
            // sinon (le produit n'est pas encore dans le panier )
            // on crée un objet panier
            $ligne = new Panier();
            // on associe cette ligne au user connecté
            $ligne->setUser($user);
            // on associe le produit a cette ligne
            $ligne->setproduit($produit);
            // on definie la quantité
            $ligne->setQuantity($quantite);

            $produit->setStock($produit->getStock()-$quantite);
            // on indique à Doctrine qu'on veut sauvgarder cette ligne de panier
            $em->persist($ligne);
        }
        // on envoie les modifications a Doctrine qui lui envoie les requetes SQL
        $em->flush();
        // message flash   
        $this->addFlash('succes', "Le produit a bien été ajouté au panier");

        // renvoye à la page précedente, le user va vers la page d'ou il vient
        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/panier/retirer/{id}', name: 'panier_retirer')]
    public function retirer() {}

    #[Route('/panier/vider/{id}', name: 'panier_vider')]
    public function vider() {}
    #[Route('/panier/modifier/{id}', name: 'panier_modifier')]
    public function modifierQuantite() {}
}
