<?php

namespace App\Controller;

use App\Form\ScheduleType;
use App\Repository\ScheduleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ScheduleController extends AbstractController
{
    #[Route('/schedule', name: 'app_schedule', methods:['GET', 'POST'])]
    public function index(Request $request, ScheduleRepository $repository, EntityManagerInterface $entityManager): Response
    {
        $schedule = $repository->getGeneral();
        $form = $this->createForm(ScheduleType::class, $schedule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($schedule);
            $entityManager->flush();
        }

        return $this->render('schedule/index.html.twig', [
            'time' => date('d.m.Y H:i:s'),
            'form' => $form,
        ]);
    }
}
