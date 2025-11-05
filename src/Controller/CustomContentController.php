<?php

namespace App\Controller;

use App\Entity\CustomContent;
use App\Form\CustomContentType;
use App\Repository\CustomContentRepository;
use App\Repository\HistoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CustomContentController extends AbstractController
{
    #[Route('/custom/content', name: 'app_custom_content')]
    public function index(
        Request $request,
        CustomContentRepository $customContentRepository,
        EntityManagerInterface $entityManager,
        HistoryRepository $history,
    ): Response
    {
        $customContent = $customContentRepository->getCurrent();

        $form = $this->createForm(CustomContentType::class, $customContent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (!$customContent->getId()) {
                $entityManager->persist($customContent);
            }

            $entityManager->flush();

            $history->write("Обновление кастомизации");

            return $this->redirectToRoute('app_custom_content');
        }

        return $this->render('custom_content/index.html.twig', [
            'form' => $form,
            'customContent' => $customContent,
        ]);
    }
}
