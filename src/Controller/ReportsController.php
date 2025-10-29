<?php

namespace App\Controller;

use Amenadiel\JpGraph\Plot\GroupBarPlot;
use App\Entity\Channel;
use App\Entity\User;
use App\Payload\ReportFilterPayload;
use App\Repository\CallReportsRepository;
use App\Repository\ChannelRepository;
use App\Repository\GraphRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReportsController extends AbstractController
{
    public function __construct(
        private CallReportsRepository $repository
    )
    {}

    #[Route('/reports', name: 'app_reports')]
    public function index(Request $request): Response
    {
        $filter = ReportFilterPayload::createFromRequest($request);

        return $this->render('reports/index.html.twig', [
            'filter' => $filter,
            'totals' => [
                'accepted' => $this->repository->getAcceptedTotals($filter),
                'rejected' => $this->repository->getRejectedTotals($filter),
            ],
        ]);
    }

    #[Route('/reports/calls', name: 'app_reports_calls')]
    public function calls(Request $request, PaginatorInterface $paginator): Response
    {
        $filter = ReportFilterPayload::createFromRequest($request);

        return $this->render('reports/calls.html.twig', [
            'filter' => $filter,
            'pagination' => $paginator->paginate(
                $this->repository
                    ->getClosed($filter)
                    ->orderBy("c.waitStart", "DESC"),
                $request->query->getInt('page', 1),
            ),
        ]);
    }

    #[Route('/reports/consultants', name: 'app_reports_consultants')]
    public function consultants(Request $request, PaginatorInterface $paginator): Response
    {
        $filter = ReportFilterPayload::createFromRequest($request);

        return $this->render('reports/consultants.html.twig', [
            'filter' => $filter,
            'pagination' => $paginator->paginate(
                $this->repository->getConsultants($filter),
                $request->query->getInt('page', 1),
            ),
        ]);
    }

    #[Route('/reports/channels', name: 'app_reports_channels')]
    public function channels(
        Request $request,
        PaginatorInterface $paginator,
    ): Response
    {
        $filter = ReportFilterPayload::createFromRequest($request);

        $pagination = $paginator->paginate(
            $this->repository->getChannelsForClosedCalls($filter)
                ->select("ch")
                ->distinct(),
            $request->query->getInt('page', 1),
        );

        $channels = $pagination->getItems();

        $result = [];

        foreach ($channels as $channel) {
            $result[$channel->getId()] = [
                'id' => $channel->getId(),
                'title' => $channel->getTitle(),
            ];
        }

        foreach ($this->repository->getRejectedCallsForChannels($filter, $channels) as $rows) {
            $result[$rows['channel']]["rejected"] = $rows['rejected'];
            $result[$rows['channel']]["avgWaitRe"] = $rows['avgWaitRe'];
            $result[$rows['channel']]["maxWaitRe"] = $rows['maxWaitRe'];
            $result[$rows['channel']]["minWaitRe"] = $rows['minWaitRe'];
        }

        foreach ($this->repository->getAcceptedCallsForChannels($filter, $channels) as $rows) {
            $result[$rows['channel']]["accepted"] = $rows['accepted'];
            $result[$rows['channel']]["avgServ"] = $rows['avgServ'];
            $result[$rows['channel']]["maxServ"] = $rows['maxServ'];
            $result[$rows['channel']]["minServ"] = $rows['minServ'];
            $result[$rows['channel']]["avgWaitAc"] = $rows['avgWaitAc'];
            $result[$rows['channel']]["maxWaitAc"] = $rows['maxWaitAc'];
            $result[$rows['channel']]["minWaitAc"] = $rows['minWaitAc'];
        }

        return $this->render('reports/channels.html.twig', [
            'filter' => $filter,
            'result' => $result,
            'pagination' => $pagination,
        ]);
    }

    #[Route('/reports/qualities', name: 'app_reports_qualities')]
    public function qualities(Request $request): Response
    {
        $filter = ReportFilterPayload::createFromRequest($request);

        return $this->render('reports/qualities.html.twig', [
            'filter' => $filter,
        ]);
    }

    #[Route('/reports/graph/hours', name: 'app_reports_graph_hours')]
    public function graphHours(
        Request $request,
        GraphRepository $graphRepository,
    ): Response
    {
        $filter = ReportFilterPayload::createFromRequest($request);

        $closed = $this->repository->getHours($this->repository->getClosed($filter));
        $rejected = $this->repository->getHours($this->repository->getRejected($filter));
        $accepted = $this->repository->getHours($this->repository->getAccepted($filter));

        $graph = $graphRepository->createGraph(400, 400, true);

        $graph->xaxis->SetTickLabels($graphRepository->getLables($closed));

        $graph->Add(new GroupBarPlot([
            $graphRepository->createBarPlot("#05588f", $closed),
            $graphRepository->createBarPlot("#ff6266", $rejected),
            $graphRepository->createBarPlot("#00a43b", $accepted),
        ]));

        return new Response($graph->Stroke(), 200, ['Content-Type' => 'image/jpeg']);
    }

    #[Route('/reports/graph/weekdays', name: 'app_reports_graph_weekdays')]
    public function graphWeekdays(
        Request $request,
        GraphRepository $graphRepository,
    ): Response
    {
        $filter = ReportFilterPayload::createFromRequest($request);

        $closed = $this->repository->getWeekdays($this->repository->getClosed($filter));
        $rejected = $this->repository->getWeekdays($this->repository->getRejected($filter));
        $accepted = $this->repository->getWeekdays($this->repository->getAccepted($filter));

        $graph = $graphRepository->createGraph(400, 400);

        $graph->xaxis->SetTickLabels($graphRepository->getLables($closed));

        $graph->Add(new GroupBarPlot([
            $graphRepository->createBarPlot("#05588f", $closed),
            $graphRepository->createBarPlot("#ff6266", $rejected),
            $graphRepository->createBarPlot("#00a43b", $accepted),
        ]));

        return new Response($graph->Stroke(), 200, ['Content-Type' => 'image/jpeg']);
    }

    #[Route('/reports/graph/channels', name: 'app_reports_graph_channels')]
    public function graphChannels(
        Request $request,
        GraphRepository $graphRepository,
    ): Response
    {
        $filter = ReportFilterPayload::createFromRequest($request);

        $closed = $this->repository->getChannels($this->repository->getClosed($filter));
        $rejected = $this->repository->getChannels($this->repository->getRejected($filter));
        $accepted = $this->repository->getChannels($this->repository->getAccepted($filter));

        $graph = $graphRepository->createGraph(400, 400, true);

        $graph->xaxis->SetTickLabels($graphRepository->getLables($closed));

        $graph->Add(new GroupBarPlot([
            $graphRepository->createBarPlot("#05588f", $closed),
            $graphRepository->createBarPlot("#ff6266", $rejected),
            $graphRepository->createBarPlot("#00a43b", $accepted),
        ]));

        return new Response($graph->Stroke(), 200, ['Content-Type' => 'image/jpeg']);
    }
}
