<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;


#[Route('/product')]
final class ProductController extends AbstractController
{
    #[Route('/api/products', name: 'api_products', methods: ['GET'])]
    public function index(ProductRepository $repository): JsonResponse
    {
        $products = $repository->findAll();
        return $this->json($products);
    }

    #[Route('/api/products/{id}', name: 'api_product_show', methods: ['GET'])]
    public function show(Product $product): JsonResponse
    {
        return $this->json($product);
    }

    #[Route('/api/products', name: 'api_product_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $product = new Product();
        $product->setName($data['name']);
        $product->setPrice($data['price']);

        $em->persist($product);
        $em->flush();

        return $this->json($product, 200, [], ['groups' => ['public']]);
    }

    #[Route('/api/products/{id}', name: 'api_product_update', methods: ['PUT'])]
    public function update(Product $product, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $product->setName($data['name'] ?? $product->getName());
        $product->setPrice($data['price'] ?? $product->getPrice());

        $em->flush();

        return $this->json($product);
    }

    #[Route('/api/products/{id}', name: 'api_product_delete', methods: ['DELETE'])]
    public function delete(Product $product, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($product);
        $em->flush();

        return $this->json(null, 204);
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        // Аутентификация через событие Symfony
        return $this->json(['token' => 'ВАШ_JWT_ТОКЕН']);
    }
}
