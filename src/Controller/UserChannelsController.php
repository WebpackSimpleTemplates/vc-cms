<?php

namespace App\Controller;

use App\Entity\Channel;
use App\Entity\User;
use App\Repository\ChannelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user/{id}/channels')]
final class UserChannelsController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[Route("/connected", name: 'app_user_channels')]
    public function index(User $user): Response
    {
        return $this->render('user/channels.html.twig', [
            'user' => $user,
            'channels' => $user->getChannels(),
        ]);
    }

    #[Route("/all", name: 'app_user_channels_all')]
    public function all(User $user, ChannelRepository $channelRepository): Response
    {
        $connectedIds = array_map(fn(Channel $ch) => $ch->getId(), (array) $user->getChannels()->toArray());

        $channels = array_map(fn ($channel) => [
            'id' => $channel->getId(),
            'prefix' => $channel->getPrefix(),
            'title' => $channel->getTitle(),
            'description' => $channel->getDescription(),
            'connected' => in_array($channel->getId(), $connectedIds),
        ], $channelRepository->findAll());

        return $this->render('user/channels-all.html.twig', [
            'user' => $user,
            'channels' => $channels,
        ]);
    }

    #[Route("/connect/{channel}", name: 'app_user_channels_connect', methods:['POST'])]
    public function connect(User $user, Channel $channel): Response
    {
        $user->addChannel($channel);

        $this->entityManager->flush();

        return $this->redirectToRoute("app_user_channels_all", ['id' => $user->getId()]);
    }

    #[Route("/disconnect/{channel}", name: 'app_user_channels_disconnect', methods:['POST'])]
    public function disconnect(User $user, Channel $channel): Response
    {
        $user->removeChannel($channel);
        
        $this->entityManager->flush();

        return $this->redirectToRoute("app_user_channels", ['id' => $user->getId()]);
    }
}
