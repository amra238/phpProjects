<?php

namespace App\Controller;

use App\Entity\Link;
use App\Entity\User;
use App\Form\LinkType;
use App\Service\LinkService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class LinkController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('link/index.html.twig', [
            'links' => $user->getLinks(),
        ]);
    }

    #[Route('/new', name: 'app_add_link', methods: ['GET', 'POST'])]
    public function new(Request $request, LinkService $linkService): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $link = new Link();
            $form = $this->createForm(LinkType::class, $link);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $link = $form->getData();
                $linkService->createFromForm($user, $link);

                return $this->redirectToRoute('app_home');
            }

            return $this->render('link/new.html.twig', [
                'form' => $form->createView(),
            ]);
        }
        catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_home');
        }
    }

    #[Route('/short/{code}', name: 'app_redirect')]
    public function redirectToUrl(string $code, LinkService $linkService): Response
    {
        try {
            $link = $linkService->processingToRedirect($code);

            return $this->redirect($link->getFullUrl());
        }
        catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_home');
        }
    }

    #[Route('/delete/{id}', name: 'app_delete', methods: ['POST'])]
    public function delete(int $id, LinkService $linkService): Response
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            $linkService->processingToDelete($id, $user);

            return $this->redirectToRoute('app_home');
        }
        catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_home');
        }
    }
}
