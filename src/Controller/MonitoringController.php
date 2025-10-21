<?php

namespace App\Controller;

use App\Repository\CallRepository;
use App\Repository\ConsultantStatusRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot\BarPlot;
use App\Repository\ChannelRepository;
use Knp\Component\Pager\Paginator;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;


final class MonitoringController extends AbstractController
{
    #[Route('/monitoring', name: 'app_monitoring')]
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

    #[Route('/monitoring/channels', name: 'app_monitoring_channels')]
    public function barsChannels(
        Request $request,
        CallRepository $callRepository,
    )
    {
        function mapValue($value) {
            if (gettype($value) === 'string') {
                $comps = explode(":", $value);

                return ((int) $comps[0]) * 60 + ((int) $comps[1]);
            }

            return $value;
        }

        $param = $request->query->get("param", "count");
        $type = $request->query->get("type", "wait");

        $results = $callRepository->getActiveChannels($type !== 'wait');

        $graph = new Graph\Graph(424,250,'auto');
        $graph->SetScale("textlin");

        $graph->SetBox(false);

        $graph->ygrid->SetFill(false);

        $graph->yaxis->HideLine(false);
        $graph->yaxis->HideTicks(false,false);

        $datay=array_map(fn ($raw) => mapValue($raw[$param]), $results);

        if (count($datay)) {
            // dd($results);
            $channelsTitles = array_map(fn($raw) => $raw['prefix'], $results);
            $graph->xaxis->SetTickLabels($channelsTitles);
            $b1plot = new BarPlot($datay);

            $graph->Add($b1plot);

            $b1plot->SetColor("white");
            $b1plot->SetFillColor("#05588f");
            // $b1plot->SetWidth(30);
        } else {
            $graph->xaxis->SetTickLabels(['']);
            $b1plot = new BarPlot([0]);

            $graph->Add($b1plot);

            $b1plot->SetColor("white");
            $b1plot->SetFillColor("white");
            $b1plot->SetWidth(1);
        }

        // Display the graph
        return $graph->Stroke();
    }
}
