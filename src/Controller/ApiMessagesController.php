<?php

namespace App\Controller;

use App\Entity\Call;
use App\Entity\Message;
use App\Entity\User;
use App\Payload\MessagePayload;
use App\Repository\MessageRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/messages/{call}')]
final class ApiMessagesController extends AbstractController
{
    #[Route('/', name: 'app_api_messages', methods:["GET"])]
    public function index(Call $call, MessageRepository $messageRepository): JsonResponse
    {
        $messages = $messageRepository->findBy(["call" => $call]);

        return $this->json($messages);
    }


    #[Route('/', name:'post_api_message', methods:['POST'])]
    public function post(
        #[MapRequestPayload] MessagePayload $payload,
        Call $call,
        EntityManagerInterface $entityManager,
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

        $entityManager->persist($message);
        $entityManager->flush();

        return new Response(null, 204);
    }

    #[Route('/read', name:'read_api_messages', methods:['PUT'])]
    public function read(
        Call $call,
        MessageRepository $messageRepository,
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

        return new Response(null, 204);
    }
}
