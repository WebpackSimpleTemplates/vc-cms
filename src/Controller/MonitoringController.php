<?php

namespace App\Controller;

use App\Repository\CallRepository;
use App\Repository\ConsultantStatusRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MonitoringController extends AbstractController
{
    #[Route('/monitoring', name: 'app_monitoring')]
    public function index(
        CallRepository $callRepository,
        ConsultantStatusRepository $consultantStatusRepository,
    ): Response
    {
        return $this->render('monitoring/index.html.twig', [
            'controller_name' => 'MonitoringController',
            "calls" => $callRepository->getActiveCounts(),
            "consultants" => $consultantStatusRepository->getCounts(),
            'callsTimes' => $callRepository->getActiveTimes(),
        ]);
    }
}
