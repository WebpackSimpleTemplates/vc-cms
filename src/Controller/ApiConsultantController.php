<?php

namespace App\Controller;

use App\Entity\Call;
use App\Entity\Channel;
use App\Entity\ConsultantStatus;
use App\Entity\User;
use App\Payload\ConsultantStatusPayload;
use App\Payload\UpdatePasswordPayload;
use App\Repository\CallRepository;
use App\Repository\ConsultantStatusRepository;
use App\Repository\HistoryRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PushRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/consultant')]
final class ApiConsultantController extends AbstractController
{
    #[Route('/calls/count', name: 'api_consultant_calls_count')]
    public function callsCount(CallRepository $callRepository, Security $security)
    {
        return $this->json([
            "count" => $callRepository->getActiveCallsCount($security->getUser()),
        ]);
    }

    #[Route('/channels', name: 'api_consultant_channels')]
    public function channels(CallRepository $callRepository, Security $security)
    {
        return $this->json($callRepository->getActiveChannelsForUser($security->getUser()));
    }

    #[Route('/accept-next/{id}', name: 'api_consultant_accept_next_by_channel')]
    public function acceptNextByChannel(
        CallRepository $callRepository,
        Security $security,
        EntityManagerInterface $entityManager,
        PushRepository $pushRepository,
        HistoryRepository $history,
        Channel $channel
    )
    {
        /** @var User $user */
        $user = $security->getUser();

        foreach ($user->getChannels() as $item) {
            if ($item->getId() === $channel->getId()) {
                return $this->acceptNext($callRepository, $security, $entityManager, $pushRepository, $history, $channel);
            }
        }

        return $this->json([
            "details" => "This channel dont connected to you"
        ], 400);
    }

    #[Route('/accept-next', name: 'api_consultant_accept_next')]
    public function acceptNext(
        CallRepository $callRepository,
        Security $security,
        EntityManagerInterface $entityManager,
        PushRepository $pushRepository,
        HistoryRepository $history,
        ?Channel $channel = null,
    )
    {
        $user = $security->getUser();
        /** @var Call $call */
        $call = $callRepository->getNextCall($user, $channel);

        if (!$call) {
            return $this->json(null, 400);
        }

        $call->accept($user);

        $entityManager->flush();

        $pushRepository->push("calls/".$call->getId(), "accepted", $call);
        $pushRepository->push("", "call-accepted", $call);

        $history->write("Взятие звонка", $call->getPrefix()." ".$call->getNum(), true);

        return $this->json($call);
    }

    #[Route('/status', methods:['PUT'])]
    public function status(
        #[MapRequestPayload] ConsultantStatusPayload $payload,
        ConsultantStatusRepository $consultantStatusRepository,
        CallRepository $callRepository,
        Security $security,
        EntityManagerInterface $entityManager,
    )
    {
        /** @var User $user */
        $user = $security->getUser();

        /** @var ConsultantStatus $raw */
        $raw = $consultantStatusRepository->findOneBy(["userLink" => $user]);

        if (!$raw) {
            $raw = new ConsultantStatus()
                ->setUserLink($user)
                ->setPauseTime(0)
                ->setServeTime(0)
                ->setWaitTime(0)
            ;

            $entityManager->persist($raw);
        }

        $raw->setLastOnline(new DateTime());

        $raw->setStatusWithIncrementTimes($payload->status, $payload->increment);

        if (!$payload->callId) {
            $raw->setCall(null);
        } else if (!$raw->getCall() || $payload->callId !== $raw->getCall()->getId()) {
            $raw->setCall($callRepository->findOneBy([ "id" => $payload->callId ]));
        }

        $entityManager->flush();

        return new Response('', 204);
    }

    #[Route('/password', methods:['PUT'], name:'api_update_password_consultant')]
    public function updatePassword(
        #[MapRequestPayload] UpdatePasswordPayload $payload,
        UserPasswordHasherInterface $userPasswordHasher,
        Security $security,
        EntityManagerInterface $entityManager,
    ): Response
    {
        /** @var User $user */
        $user = $security->getUser();

        $user->setPassword($userPasswordHasher->hashPassword($user, $payload->password));

        $entityManager->flush();

        return new Response('', 204);
    }

    #[Route('/defer', name:'api_defer_calls')]
    public function getDeferCalls(
        Security $security,
        CallRepository $callRepository
    )
    {
        /** @var User $user */
        $user = $security->getUser();

        return $this->json($callRepository->getDeferCalls($user));
    }
}
