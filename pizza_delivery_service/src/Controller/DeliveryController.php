<?php

namespace App\Controller;

use App\Entity\Delivery;
use App\Entity\Point;
use App\Form\PointType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DeliveryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    #[Route('/delivery/new', name: 'delivery_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $point = new Point();
        $form = $this->createForm(PointType::class, $point);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $delivery = new Delivery();
                $delivery->setPointOfDelivery($point);

                $this->entityManager->persist($delivery);
                $this->entityManager->flush();

                return $this->render('delivery/viewData.html.twig', [
                    'delivery' => $delivery,
                ]);
            } catch (\Throwable $e) {
                $this->addFlash('danger', $e->getMessage());
                return $this->redirectToRoute('delivery_new');
            }
        }

        return $this->render('delivery/new.html.twig', [
            'form' => $form,
        ]);
    }
}
