<?php

namespace App\Controller;

use App\Entity\Quality;
use App\Form\QualityType;
use App\Repository\HistoryRepository;
use App\Repository\QualityRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/manage/quality')]
final class QualityController extends AbstractController
{
    #[Route(name: 'app_quality_index', methods: ['GET'])]
    public function index(QualityRepository $qualityRepository, PaginatorInterface $paginator, Request $request): Response
    {
        return $this->render('quality/index.html.twig', [
            'pagination' => $paginator->paginate(
                $qualityRepository->getMany(),
                $request->query->getInt("page", 1),
                10,
            ),
        ]);
    }

    #[Route('/new', name: 'app_quality_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, HistoryRepository $history): Response
    {
        $quality = new Quality();
        $form = $this->createForm(QualityType::class, $quality);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($quality);
            $entityManager->flush();

            $history->write("Создание оценки качества", $quality->getTitle());

            return $this->redirectToRoute('app_quality_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('quality/new.html.twig', [
            'quality' => $quality,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_quality_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Quality $quality, EntityManagerInterface $entityManager, HistoryRepository $history): Response
    {
        if ($quality->getDeletedAt()) {
            return $this->render('quality/deleted.html.twig', [
                'quality' => $quality,
            ]);
        }

        $oldTitle = $quality->getTitle();
        $form = $this->createForm(QualityType::class, $quality);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $history->write("Редактирование оценки качества", $oldTitle." -> ".$quality->getTitle());

            return $this->redirectToRoute('app_quality_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('quality/edit.html.twig', [
            'quality' => $quality,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_quality_delete', methods: ['POST'])]
    public function delete(Request $request, Quality $quality, EntityManagerInterface $entityManager, HistoryRepository $history): Response
    {
        if (!$quality->getDeletedAt() && $this->isCsrfTokenValid('delete'.$quality->getId(), $request->getPayload()->getString('_token'))) {
            $quality->setDeletedAt(new DateTime());
            $quality->setDeletedBy($this->getUser());
            $entityManager->flush();
            $history->write("Удаление оценки качества", $quality->getTitle());
        }

        return $this->redirectToRoute('app_quality_index', [], Response::HTTP_SEE_OTHER);
    }
}
