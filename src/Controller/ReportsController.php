<?php

namespace App\Controller;

use Amenadiel\JpGraph\Graph\Graph;
use Amenadiel\JpGraph\Plot\GroupBarPlot;
use App\Entity\Call;
use App\Payload\ReportFilterPayload;
use App\Repository\CallReportsRepository;
use App\Repository\GraphRepository;
use Knp\Component\Pager\Paginator;
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

    #[Route('/reports/call/{call}', name:'app_call_report')]
    public function call(Call $call)
    {
        return $this->render('reports/call.html.twig', [
            'time' => date('d.m.Y H:i:s'),
            'call' => $call,
        ]);
    }

    #[Route('/reports', name: 'app_reports')]
    public function index(Request $request, PaginatorInterface $paginator): Response
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

        $pagination = $paginator->paginate(
            $this->repository
                ->getClosed($filter)
                ->orderBy("c.waitStart", "DESC"),
            $request->query->getInt('page', 1),
        );

        $calls = $pagination->getItems();

        $result = [];

        $rates = $this->repository->getRatesForCalls(array_map(fn(Call $raw) => $raw->getId(), $calls));

        foreach ($calls as $call) {
            /** @var Call $call */

            $raw = [
                'id' => $call->getId(),
                'formatWaitStart' => $call->formatWaitStart(),
                'accepted' => !!$call->getAcceptedAt(),
                'channel' => $call->getChannel()->getTitle(),
                'consultant' => $call->getConsultantName(),
                'duration' => $call->getDuration(),
                'quality' => null,
                'num' => $call->getPrefix().' '.$call->getNum(),
            ];

            foreach ($rates as $rate) {
                if ($raw['id'] == $rate['id']) {
                    $raw['quality'] = $rate['quality'];
                    break;
                }
            }

            $result[] = $raw;
        }

        return $this->render('reports/calls.html.twig', [
            'filter' => $filter,
            'pagination' => $pagination,
            'result' => $result,
        ]);
    }

    #[Route('/reports/consultants', name: 'app_reports_consultants')]
    public function consultants(Request $request, PaginatorInterface $paginator): Response
    {
        $filter = ReportFilterPayload::createFromRequest($request);

        $pagination = $paginator->paginate(
            $this->repository->getConsultants($filter),
            $request->query->getInt('page', 1),
        );

        $result = $pagination->getItems();

        $rates = $this->repository->getRatesForConsultants(array_map(fn(array $raw) => $raw['id'], $result), $filter);

        for ($i=0; $i < count($result); $i++) {
            $result[$i]['quality'] = null;
            foreach ($rates as $rate) {
                if ($result[$i]['id'] == $rate['id']) {
                    $result[$i]['quality'] = $rate['quality'];
                    break;
                }
            }
        }

        return $this->render('reports/consultants.html.twig', [
            'filter' => $filter,
            'pagination' => $pagination,
            'result' => $result,
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
            $result[$rows['channel']]["avgWaitRe"] = $this->repository->fInter($rows['avgWaitRe']);
            $result[$rows['channel']]["maxWaitRe"] = $rows['maxWaitRe'];
            $result[$rows['channel']]["minWaitRe"] = $rows['minWaitRe'];
        }

        foreach ($this->repository->getAcceptedCallsForChannels($filter, $channels) as $rows) {
            $result[$rows['channel']]["accepted"] = $rows['accepted'];
            $result[$rows['channel']]["avgServ"] = $this->repository->fInter($rows['avgServ']);
            $result[$rows['channel']]["maxServ"] = $rows['maxServ'];
            $result[$rows['channel']]["minServ"] = $rows['minServ'];
            $result[$rows['channel']]["avgWaitAc"] = $this->repository->fInter($rows['avgWaitAc']);
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
    public function qualities(PaginatorInterface $paginator, Request $request): Response
    {
        $filter = ReportFilterPayload::createFromRequest($request);

        return $this->render('reports/qualities.html.twig', [
            'filter' => $filter,
            'pagination' => $paginator->paginate(
                $this->repository->getQualitiesResults($filter)->select('qr'),
                $request->query->getInt('page', 1),
            ),
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

    #[Route('/reports/graph/qualities', name: 'app_reports_graph_qualities')]
    public function graphQualities(
        Request $request,
        GraphRepository $graphRepository,
    ): Response
    {
        $filter = ReportFilterPayload::createFromRequest($request);

        $qualities =$this->repository->getQualities($filter)
            ->select([
                "q.title as title",
                "AVG(qr.value) as avg",
            ])
            ->groupBy("q.id", "q.title")
            ->getQuery()
            ->getResult()
        ;

        $data = [];

        foreach ($qualities as $quality) {
            $data[$quality['title']] = (float) $quality['avg'];
        }

        $graph = new Graph(750,600,'auto');
        $graph->SetScale("textlin", 0, 10);

        $graph->Set90AndMargin(150,20,30,10);
        $graph->SetBox(false);

        $graph->ygrid->SetFill(false);

        $graph->yaxis->HideLine(false);
        $graph->yaxis->HideTicks(false,false);

        $graph->xaxis->SetTickLabels($graphRepository->getLables($data));

        $graph->Add($graphRepository->createBarPlot("#05588f", $data));

        return new Response($graph->Stroke(), 200, ['Content-Type' => 'image/jpeg']);
    }
}
