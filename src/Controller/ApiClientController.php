<?php

namespace App\Controller;

use App\Entity\Call;
use App\Entity\Channel;
use App\Payload\StartCallPayload;
use App\Repository\CallRepository;
use App\Repository\HistoryRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PushRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/client')]
final class ApiClientController extends AbstractController
{
    #[Route('/avg-wait-time', name: 'avg_wait_time')]
    public function avgWaitTime(CallRepository $repository)
    {
        return $this->json([
            "time" => $repository->getAvgWaitTime(),
        ]);
    }

    #[Route('/start/{channel}', name: 'api_start_call', methods:['POST'])]
    public function index(
        #[MapRequestPayload] StartCallPayload $payload,
        CallRepository $callRepository,
        EntityManagerInterface $entityManager,
        Channel $channel,
        PushRepository $pushRepository,
    ): JsonResponse
    {
        $call = new Call();

        $call->setNum($callRepository->getNextNum($channel->getPrefix()));
        $call->setPrefix($channel->getPrefix());
        $call->setChannel($channel);
        $call->setType($payload->type);
        $call->setWaitStart(new DateTime());
        $call->setHour((int) date("H"));
        $call->setWeekday((int) date("w"));

        $entityManager->persist($call);
        $entityManager->flush();

        $pushRepository->push("", "call-created", $call);

        return $this->json($call);
    }

    #[Route('/{call}', name: 'api_call_info')]
    public function call(Call $call)
    {
        return $this->json($call);
    }

    #[Route('/{call}/close', name:'api_close_call')]
    public function closeCall(
        Call $call,
        EntityManagerInterface $entityManager,
        PushRepository $pushRepository,
        HistoryRepository $history,
    )
    {
        if (!$call->getClosedAt()) {
            $call->setClosedAt(new DateTime());

            $pushRepository->push("calls/".$call->getId(), "closed", $call);
            $pushRepository->push("", "call-closed", $call);

            $history->write("Завершение звонка", $call->getPrefix()." ".$call->getNum(), true);

            $entityManager->flush();
        }

        return new Response(null, 204);
    }
}
