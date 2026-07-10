<?php
namespace App\Controller;

use phpDocumentor\Reflection\DocBlock\Tags\Extends_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[Route('/details', name: 'details_page')]
    public function index(): Response
    {
        return $this->render('page/homepage.html.twig', [
            'username' => 'Максим',
            'products' => ['Ноутбук', 'Телефон', 'Наушники'],
            'users' => ['илья', 'Андрей', 'марат']
        ]);
    }

    public function hello(): Response
    {
        return new Response('Привет, Symfony!');
    }
}
