<?php

namespace App\Controller;

use App\Repository\CallRepository;
use App\Repository\ConsultantStatusRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Repository\ChannelRepository;
use App\Repository\GraphRepository;
use Knp\Component\Pager\Paginator;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;


final class MonitoringController extends AbstractController
{
    #[Route('/manage/monitoring', name: 'app_monitoring')]
    public function index(
        CallRepository $callRepository,
        ConsultantStatusRepository $consultantStatusRepository,
        ChannelRepository $channelRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response
    {
        return $this->render('monitoring/index.html.twig', [
            'time' => date('d.m.Y H:i:s'),
            "callsCounts" => $callRepository->getActiveCounts(),
            "consultantsCounts" => $consultantStatusRepository->getCounts(),
            "channelsCounts" => $channelRepository->getCounts(),
            'callsTimes' => $callRepository->getActiveTimes(),
            'calls' => $paginator->paginate(
                $callRepository->getActiveMany(),
                $request->query->getInt('callsPage', 1),
                10,
                [
                    Paginator::PAGE_PARAMETER_NAME => 'callsPage',
                ]
            ),
            'consultants' => $paginator->paginate(
                $consultantStatusRepository->getOnlineMany(),
                $request->query->getInt('consultantsPage', 1),
                10,
                [
                    Paginator::PAGE_PARAMETER_NAME => 'consultantsPage',
                ]
            ),
        ]);
    }

    #[Route('/manage/monitoring/channels', name: 'app_monitoring_channels')]
    public function barsChannels(
        Request $request,
        CallRepository $callRepository,
        GraphRepository $graphRepository,
    )
    {
        $graph = $graphRepository->createGraph(424, 250);

        $param = $request->query->get("param", "count");
        $type = $request->query->get("type", "wait");

        $results = $callRepository->getActiveChannels($type !== 'wait');

        $data = [];

        foreach ($results as $raw) {
            $data[$raw['prefix']] = $raw[$param];
        }

        $graph->xaxis->SetTickLabels($graphRepository->getLables($data));

        $graph->Add($graphRepository->createBarPlot("#05588f", $data));

        return new Response($graph->Stroke(), 200, ['Content-Type' => 'image/jpeg']);
    }
}
