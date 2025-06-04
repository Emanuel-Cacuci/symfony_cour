<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET', 'POST'])]
    public function index(Request $request, ProduitRepository $produitRepository): Response
    {

dump($produitRepository->findAll());

        return $this->render('home/index.html.twig', [
          'produits' => $produitRepository->findAll(),

        ]);
   

    // methode find($id)
    $produit=$produitRepository->find(1);
       
    dump($produitRepository);

    $selectedProduit=null;

    if($request->isMethod('POST')){// si form est POST


        $formType=$request->request->get('form');

        if($formType==='select_produit'){// le name form dans le formulaire

            $idProduit=$request->get('produit'); // recupere l'id du produit
            $selectedProduit=$produitRepository->find($idProduit);
        }
    }


    return $this->render('home/index.html.twig', [
          "produit" =>$produit,
          "oneProduit" =>$oneProduit,
          "selectedProduit" =>$selectedProduit,


        ]);

 }


    #[Route('/apropos', name: 'apropos')]
    public function apropos(): Response
    {
        return $this->render('apropos/index.html.twig');
    }
  

 


#[Route('/exo', name: 'exo')]
public function exo(): Response
{
    return $this->render('exo/index.html.twig');
}


}