<?php

namespace App\Controller;

use App\Entity\Channel;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/channel/{id}/users')]
final class ChannelUsersController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[Route("/connected", name: 'app_channel_users')]
    public function index(Channel $channel): Response
    {
        return $this->render('channel/users.html.twig', [
            'channel' => $channel,
            'users' => $channel->getUsers(),
        ]);
    }

    #[Route("/all", name: 'app_channel_users_all')]
    public function all(Channel $channel, UserRepository $userRepository): Response
    {
        $connectedIds = array_map(fn(User $ch) => $ch->getId(), $channel->getUsers()->toArray());

        $users = array_map(fn (User $user) => [
            'id' => $user->getId(),
            'fullname' => $user->getFullname(),
            'connected' => in_array($user->getId(), $connectedIds),
        ], $userRepository->getOperators());

        return $this->render('channel/users-all.html.twig', [
            'channel' => $channel,
            'users' => $users,
        ]);
    }

    #[Route("/connect/{user}", name: 'app_channel_users_connect', methods:['POST'])]
    public function connect(Channel $channel, User $user): Response
    {
        $channel->addUser($user);

        $this->entityManager->flush();

        return $this->redirectToRoute("app_channel_users_all", ['id' => $channel->getId()]);
    }

    #[Route("/disconnect/{user}", name: 'app_channel_users_disconnect', methods:['POST'])]
    public function disconnect(Channel $channel, User $user): Response
    {
        $channel->removeUser($user);
        
        $this->entityManager->flush();

        return $this->redirectToRoute("app_channel_users", ['id' => $channel->getId()]);
    }
}
