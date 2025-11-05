<?php

namespace App\Controller;

use App\Entity\IpBlock;
use App\Entity\Call;
use App\Entity\Channel;
use App\Entity\Quality;
use App\Entity\QualityResponse;
use App\Payload\QualityPayload;
use App\Payload\StartCallPayload;
use App\Repository\CallRepository;
use App\Repository\HistoryRepository;
use App\Repository\IpBlockRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PushRepository;
use App\Repository\QualityRepository;
use App\Repository\QualityResponseRepository;
use App\Repository\ScheduleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/client')]
final class ApiClientController extends AbstractController
{
    #[Route('/avg-wait-time', name: 'avg_wait_time', stateless:true)]
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
        IpBlockRepository $ipBlockRepository,
        ScheduleRepository $scheduleRepository
    ): Response
    {
        $ip = $this->getIp();

        /** @var IpBlock $block */
        $block = $ipBlockRepository->findOneBy(["ip" => $ip]);

        if ($block) {
            return new Response($block->getPublicReason(), 403);
        }

        $schedule = $channel->getSchedule() ?? $scheduleRepository->getGeneral();

        if (!$schedule->isActive()) {
            return $this->json($schedule->getTimes(), 410);
        }

        $call = new Call();

        $call->setNum($callRepository->getNextNum($channel->getPrefix()));
        $call->setPrefix($channel->getPrefix());
        $call->setChannel($channel);
        $call->setType($payload->type);
        $call->setWaitStart(new DateTime());
        $call->setHour((int) date("H"));
        $call->setWeekday((int) date("w"));
        $call->setIp($ip);

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

    #[Route('/{call}/qualities', name:'api_get_qualities')]
    public function getQualities(Call $call, QualityRepository $qualityRepository)
    {
        return $this->json($qualityRepository->getQualitiesForCall($call));
    }

    #[Route('/{call}/quality/{quality}', name:'api_add_quality_response', methods:['POST'])]
    public function addQualityResponse(
        #[MapRequestPayload()] QualityPayload $payload,
        Call $call,
        Quality $quality,
        EntityManagerInterface $entityManager,
        QualityResponseRepository $repository
    )
    {
        $old = $repository->findOneBy(["call" => $call, "quality" => $quality]);

        if ($old) {
            $entityManager->remove($old);
        }

        $response = new QualityResponse();

        $response->setValue($payload->quality);
        $response->setQuality($quality);
        $response->setCall($call);
        $response->setChannel($call->getChannel());
        $response->setConsultant($call->getConsultant());

        $entityManager->persist($response);

        $entityManager->flush();

        return new Response('', 204);
    }

    private function getIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        if (!empty($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }

        return '';
    }
}
