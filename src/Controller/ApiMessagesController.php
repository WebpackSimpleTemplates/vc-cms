<?php

namespace App\Controller;

use App\Entity\Call;
use App\Entity\Message;
use App\Entity\User;
use App\Payload\MessagePayload;
use App\Repository\MessageRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PushRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/messages/{call}')]
final class ApiMessagesController extends AbstractController
{
    #[Route('', name: 'api_messages', methods:["GET"])]
    public function index(Call $call, MessageRepository $messageRepository): JsonResponse
    {
        $messages = $messageRepository->findBy(["call" => $call]);

        return $this->json($messages);
    }
    #[Route('/count', name: 'api_messages_count', methods:["GET"])]
    public function count(Call $call, MessageRepository $messageRepository): JsonResponse
    {
        $count = $messageRepository->count(["call" => $call]);

        return $this->json(["count" => $count]);
    }


    #[Route('', name:'api_send_message', methods:['POST'])]
    public function post(
        #[MapRequestPayload] MessagePayload $payload,
        Call $call,
        EntityManagerInterface $entityManager,
        PushRepository $pushRepository,
        Security $security,
    )
    {
        $message = new Message();

        $message->setStatus(1);

        $message->setMessage($payload->message);
        $message->setFileSize($payload->fileSize);
        $message->setFilePath($payload->filePath);
        $message->setFileName($payload->fileName);
        $message->setImageUrl($payload->imageUrl);
        $message->setTimeStamp(new DateTime());
        $message->setCall($call);

        /** @var User $user */
        $user = $security->getUser();

        if ($user) {
            $message->setAuthorId($user->getId());
            $message->setName($user->getFullname());
        } else {
            $message->setName("Клиент");
        }

        $pushRepository->push("calls/messages/".$call->getId(), "message", $message);

        $entityManager->persist($message);
        $entityManager->flush();

        return $this->json($message);
    }

    #[Route('/read', name:'api_read_messages', methods:['PUT', 'POST'])]
    public function read(
        Call $call,
        MessageRepository $messageRepository,
        PushRepository $pushRepository,
        Security $security,
    )
    {
        $qb = $messageRepository->createQueryBuilder("m")
            ->update()
            ->set("m.status", 2)
            ->where("m.call = :call")
            ->setParameter("call", $call);

        /** @var User $user */
        $user = $security->getUser();

        if ($user) {
            $qb
                ->andWhere("m.authorId != :authorId")
                ->setParameter("authorId", $user->getId());
        } else {
            $qb
                ->andWhere("m.authorId IS NOT NULL");
        }

        $qb->getQuery()->execute();

        $pushRepository->push("calls/messages/".$call->getId(), "read-messages", $user ? [ "authorId" => $user->getId() ] : []);

        return new Response('', 204);
    }
}
