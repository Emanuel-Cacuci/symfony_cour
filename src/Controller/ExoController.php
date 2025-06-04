<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Doctrine\ORM\Repository\Exception\InvalidFindByCall;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ExoController extends AbstractController
{
    #[Route('/exo', name: 'app_exo')]
    public function index(ProduitRepository $produitRepository): Response
    {

        $produit1 = $produitRepository->findOneBy(['nom' => 'Chemise']);
        $produit2 = $produitRepository->findBy(['category' => '1']);
        $produit3 = $produitRepository->findOneBy(['stock' => '10']);
        $produit4 = $produitRepository->findBy([], ['Prix' => 'DESC']);

dump($produit2);
        return $this->render('exo/index.html.twig', [

            'produitx' => $produit2,
        ]);

    }

}

