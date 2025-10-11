<?php

namespace App\Controller;

use App\Entity\Call;
use App\Entity\Channel;
use App\Entity\User;
use App\Repository\CallRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/consultant')]
final class ApiConsultantController extends AbstractController
{
    #[Route('/calls/count', name: 'app_api_consultant_calls_count')]
    public function callsCount(CallRepository $callRepository)
    {
        return $this->json([
            "count" => $callRepository->count(["closedAt" => null, "acceptedAt" => null]),
        ]);
    }

    #[Route('/channels', name: 'app_api_consultant_channels')]
    public function channels(CallRepository $callRepository, Security $security)
    {
        return $this->json($callRepository->getActiveChannelsForUser($security->getUser()));
    }

    #[Route('/accept-next/{id}', name: 'app_api_consultant_accept_next_by_channel')]
    public function acceptNextByChannel(CallRepository $callRepository, Security $security, EntityManagerInterface $entityManager, Channel $channel)
    {
        /** @var User $user */
        $user = $security->getUser();

        $channels = $user->getChannels();

        foreach ($channels as $item) {
            if ($item->getId() === $channel->getId()) {
                return $this->acceptNext($callRepository, $security, $entityManager, $channel);
            }
        }

        return $this->json([
            "details" => "This channel dont connected to you"
        ], 400);
    }

    #[Route('/accept-next', name: 'app_api_consultant_accept_next')]
    public function acceptNext(CallRepository $callRepository, Security $security, EntityManagerInterface $entityManager, ?Channel $channel = null)
    {
        $user = $security->getUser();
        /** @var Call $call */
        $call = $callRepository->getNextCall($user, $channel);

        if (!$call) {
            return $this->json(null);
        }

        $call->accept($user);

        return $this->json($call);
    }
}
