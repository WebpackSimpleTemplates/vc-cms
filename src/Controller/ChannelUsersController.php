<?php

namespace App\Controller;

use App\Entity\Channel;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/channel/{id}/users')]
final class ChannelUsersController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[Route("/connected", name: 'app_channel_users')]
    public function index(Channel $channel, UserRepository $userRepository, PaginatorInterface $paginator, Request $request): Response
    {
        return $this->render('channel/users.html.twig', [
            'channel' => $channel,
            'pagination' => $paginator->paginate(
                $userRepository->getChannelUsersQuery($channel),
                $request->query->getInt("page", 1),
                10,
            ),
        ]);
    }

    #[Route("/all", name: 'app_channel_users_all')]
    public function all(Channel $channel, UserRepository $userRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $pagination = $paginator->paginate(
            $userRepository->getMany(),
            $request->query->getInt("page", 1),
            10,
        );

        $currentItemsIds = array_map(fn(User $us) => $us->getId(), (array) $pagination->getItems());

        $connected = $userRepository
            ->getChannelUsersQuery($channel)
            ->andWhere("c.id IN(:ids)")
            ->setParameter("ids", $currentItemsIds)
            ->getQuery()
            ->getResult();

        return $this->render('channel/users-all.html.twig', [
            'channel' => $channel,
            'pagination' => $pagination,
            'connectedIds' => array_map(fn(User $us) => $us->getId(), (array) $connected),
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
