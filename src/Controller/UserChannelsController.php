<?php

namespace App\Controller;

use App\Entity\Channel;
use App\Entity\User;
use App\Repository\ChannelRepository;
use App\Repository\HistoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user/{id}/channels')]
final class UserChannelsController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[Route("/connected", name: 'app_user_channels')]
    public function index(User $user, ChannelRepository $channelRepository, PaginatorInterface $paginator, Request $request): Response
    {
        return $this->render('user/channels.html.twig', [
            'user' => $user,
            'pagination' => $paginator->paginate(
                $channelRepository->getUserChannelsQuery($user),
                $request->query->getInt("page", 1),
                10,
            ),
        ]);
    }

    #[Route("/all", name: 'app_user_channels_all')]
    public function all(User $user, ChannelRepository $channelRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $pagination = $paginator->paginate(
            $channelRepository->getMany(),
            $request->query->getInt("page", 1),
            10,
        );

        $currentItemsIds = array_map(fn(Channel $ch) => $ch->getId(), (array) $pagination->getItems());

        $connected = $channelRepository
            ->getUserChannelsQuery($user)
            ->andWhere("c.id IN(:ids)")
            ->setParameter("ids", $currentItemsIds)
            ->getQuery()
            ->getResult();

        return $this->render('user/channels-all.html.twig', [
            'user' => $user,
            'pagination' => $pagination,
            'connectedIds' => array_map(fn(Channel $ch) => $ch->getId(), (array) $connected),
        ]);
    }

    #[Route("/connect/{channel}", name: 'app_user_channels_connect', methods:['POST'])]
    public function connect(User $user, Channel $channel, HistoryRepository $history): Response
    {
        $user->addChannel($channel);

        $this->entityManager->flush();
        $history->writeConnecting($user, $channel);

        return $this->redirectToRoute("app_user_channels_all", ['id' => $user->getId()]);
    }

    #[Route("/disconnect/{channel}", name: 'app_user_channels_disconnect', methods:['POST'])]
    public function disconnect(User $user, Channel $channel, HistoryRepository $history): Response
    {
        $user->removeChannel($channel);

        $this->entityManager->flush();
        $history->writeDisconnecting($user, $channel);

        return $this->redirectToRoute("app_user_channels", ['id' => $user->getId()]);
    }
}
