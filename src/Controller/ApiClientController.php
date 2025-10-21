<?php

namespace App\Controller;

use App\Entity\Call;
use App\Entity\Channel;
use App\Payload\StartCallPayload;
use App\Repository\CallRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/client')]
final class ApiClientController extends AbstractController
{
    #[Route('/start/{channel}', name: 'post_api_call', methods:['POST'])]
    public function index(
        #[MapRequestPayload] StartCallPayload $payload,
        CallRepository $callRepository,
        EntityManagerInterface $entityManager,
        Channel $channel
    ): JsonResponse
    {
        $call = new Call();

        $call->setNum($callRepository->getNextNum($channel->getPrefix()));
        $call->setPrefix($channel->getPrefix());
        $call->setChannel($channel);
        $call->setType($payload->type);
        $call->setWaitStart(new DateTime());

        $entityManager->persist($call);
        $entityManager->flush();

        return $this->json($call);
    }
}
