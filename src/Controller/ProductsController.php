<?php

namespace App\Controller;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/produits', name: 'products_')]
class ProductsController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'app_index_product')]
    public function index(): Response
    {
        $product = $this->entityManager->getRepository(Products::class)->findAll();
        return $this->render('products/details.html.twig', [
            'product' =>$product
         ]);
    }

    #[Route('/{slug}', name: 'details')]
    public function details(Products $product): Response 
    {
            
    $sizes = $product->getSize();
   

    return $this->render('products/details.html.twig', [
        'product' => $product,
        'sizes' => $sizes
    ]);
    }



    #[Route('', name: 'liste_produit')]
    public function productList(ProductsRepository $productsRepository )
    {
        return $this->render('pages/boutique.html.twig', [
            'products' => $productsRepository->findAll(),
        ]);
    }

    
    
}