<?php

namespace App\Controller;

use App\Entity\Point;
use App\Form\PointType;
use App\Service\DeliveryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DeliveryController extends AbstractController
{
    public function __construct(
        private DeliveryService $deliveryService,
    ) {
    }

    #[Route('/delivery/new', name: 'delivery_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $point = new Point();
        $form = $this->createForm(PointType::class, $point);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $delivery = $this->deliveryService->createCheapestDelivery($point);

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
