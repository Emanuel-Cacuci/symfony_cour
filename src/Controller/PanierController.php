<?php
 
namespace App\Controller;
 
use App\Entity\Panier;
use App\Entity\Produit;
use App\Entity\User;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Dom\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
 
final class PanierController extends AbstractController
{
    #[Route('/panier', name: 'panier')]
    public function index( PanierRepository $repo,SessionInterface $session ): Response
    {
        dump($this->getUser());
 
 
        if($this->getUser()){
 
            // on recupère les paniers liés au user connecté
            $paniers=$repo->findBy(['user'=>$this->getUser()]);
            // findBy() est une methode de PanierRepository, cherche dans le Panier toutes les lignes ou la collone user correspond au user connecté
            // montre moi tous les paniers appartenant aux user
    
            // total des paniers
            $total = 0;
    
    foreach ($paniers as $panier) {
        $total += $panier->getQuantity() * $panier->getProduit()->getPrix();
 

 
 
        }
}else{
// Utilisateur non connecté : panier dans la session
           $paniers = $session->get('panier', []);
      $total = 0;
          foreach ($paniers as $item) {
               $total += $item['quantity'] * $item['produit']['prix'];
           }
 
 
}
 
dump($paniers);
 
// foreach($paniers as $panier){
 
 
// }
 
        return $this->render('panier/index.html.twig', [
            "paniers"=>$paniers,
            "total"=>$total
                    ]);
    }
 
 
#[Route('/panier/ajouter/{id}', name: 'panier_ajouter')]
    public function ajouter(Request $request, Produit $produit,EntityManagerInterface $em,PanierRepository $repo,SessionInterface $session):Response
    {
 
        // Request  : represente les requetes HTTP (GET POST PUT DELETE)
        // Produit : represente le produit que l'on veut ajouter au panier
        // EntityManagerInterface : represente la connexion a la base de donnees (UPDATE DELETE INSERT SELECT)
        // PanierRepository : permet de recupérer le panier de l'utilisateur connecté
 
 
        // dump($request->query);
       $user=$this->getUser();// je recupère le user qui est connecté
 
       //on recupère la quantité demandé par le user via url (METHODE GET)
       // si aucune quantité n'es pas renseigné on va prendre 1 par defaut
       $quantite=max(1,$request->query->get('quantite',1));
    //    dump($request->query);
    //    dump($quantite);
 
        $panier=$session->get('panier',[]);// création de la variable panier
        // $panier=[ ] // tableau vide
 
    if($user){
 
        // on cheche si une ligne de panier existe deja pour cet utilisateur et ce produit
        $ligne=$repo->findOneBy([ 'user'=>$user,'produit'=>$produit]);  
    
        // methode findOneBy(), prend un tableau en argurment, c'est un tableau associatif, la clé est le nom d'un champ ou d'une propriéte de l'entité
        // et la valeur est la valeur de ce champ ou de cette propriéte
    
    
        if($ligne){
          // si une ligne existe dejà dans le repsository
         // on ajoute la quantité demandé a quantité existante
         $ligne->setQuantity($ligne->getQuantity()+$quantite);
         $produit->setStock($produit->getStock()-$quantite);
         
     }else{
         // sinon (le produit n'est pas encore dans le panier )
         // on créé un objet panier
         $ligne=new Panier();
         // on associe cette ligne au user connecté
         $ligne->setUser($user);
         // on associe le produit a cette ligne
         $ligne->setProduit($produit);
         // on definie la quantité
         $ligne->setQuantity($quantite);
         $produit->setStock($produit->getStock()-$quantite);
         
         // on indique a Doctrine qu'on veut sauvagarder cette ligne de panier
         $em->persist($ligne);
         
     }
     // on envoie les modifications a Doctrine qui lui envoie les requetes SQL
     $em->flush();
 
 
 
 
    }else{

        // quand le user n'est pas connecté nous utilisons la session pour stocker temporairement en local et non dans la base de données 
 
if(isset($panier[$produit->getId()])){ // si le produit existe déja dans le panier 
    // le tableu indexé sur l'id du produit
    // 'panier' est la clé dans laquelle nous stockons le panier($panier)
    
 
    $panier[$produit->getId()]['quantity'] +=$quantite; 



    // $panier[$produit->getId()]['quantity'] = $panier[$produit->getId()]['quantity'] + $quantite;
 
}else{
 
    $panier[$produit->getId()]=[ 
       'produit'=>[
           'id'=>$produit->getId(),
           'nom'=>$produit->getNom(),
           'prix'=>$produit->getPrix(),
         
 
 
       ],
       'quantity'=>$quantite
    ];
    
}
 
$session->set('panier', $panier);
 
    }
 
    // message flash
    $this->addFlash('success', "Le produit a bien été ajouté au panier");
 
   // renvoi a la page précedente , le user va vers la page d'où il vient
    return $this->redirect($request->headers->get('referer'));
 
    }
 
 
 
 
 
 

 
 
 
#[Route('/panier/retirer/{id}', name: 'panier_retirer')]
public function retirer(Panier $panier, EntityManagerInterface $em)
{
    // supprime la ligne de panier
    $em->remove($panier);
    $em->flush();

    return $this->redirectToRoute('panier');



}
 
#[Route('/panier/vider', name: 'panier_vider')]
public function vider(PanierRepository $repo, EntityManagerInterface $em):Response
{
 
$paniers=$repo->findby(['user'=>$this->getUser()]);

foreach($paniers as $panier){
    $em->remove($panier);
    $em->flush();
    
}
return $this->redirectToRoute('panier');
}
   // Modifie la quantité d'un produit dans le panier
    #[Route('/panier/modifier/{id}', name: 'panier_modifier_quantite', methods: ['POST'])]
    public function modifierQuantite(Panier $ligne, Request $request, EntityManagerInterface $em): Response
    {
        // On récupère la nouvelle quantité depuis le formulaire
        $quantite = max(1, (int) $request->request->get('quantite'));
 
        // Mise à jour de la quantité
        $ligne->setQuantity($quantite);
        $em->flush(); // Enregistre la mise à jour
 
        $this->addFlash('success', 'Quantité mise à jour.');
        return $this->redirectToRoute('panier_index');
    }
 
 
 
}
 