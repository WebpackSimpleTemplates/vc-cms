<?php

namespace App\Controller;

use App\Entity\Channel;
use App\Entity\Quality;
use App\Repository\ChannelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/quality/{id}/channels')]
final class QualityChannelsController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[Route("/connected", name: 'app_quality_channels')]
    public function index(Quality $quality, ChannelRepository $channelRepository, PaginatorInterface $paginator, Request $request): Response
    {
        return $this->render('quality/channels.html.twig', [
            'quality' => $quality,
            'pagination' => $paginator->paginate(
                $channelRepository->getQualityChannelsQuery($quality),
                $request->query->getInt("page", 1),
                10,
            ),
        ]);
    }

    #[Route("/all", name: 'app_quality_channels_all')]
    public function all(Quality $quality, ChannelRepository $channelRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $pagination = $paginator->paginate(
            $channelRepository->getMany(),
            $request->query->getInt("page", 1),
            10,
        );

        $currentItemsIds = array_map(fn(Channel $ch) => $ch->getId(), (array) $pagination->getItems());

        $connected = $channelRepository
            ->getQualityChannelsQuery($quality)
            ->andWhere("c.id IN(:ids)")
            ->setParameter("ids", $currentItemsIds)
            ->getQuery()
            ->getResult();

        return $this->render('quality/channels-all.html.twig', [
            'quality' => $quality,
            'pagination' => $pagination,
            'connectedIds' => array_map(fn(Channel $ch) => $ch->getId(), (array) $connected),
        ]);
    }

    #[Route("/connect/{channel}", name: 'app_quality_channels_connect', methods:['POST'])]
    public function connect(Quality $quality, Channel $channel): Response
    {
        $quality->addChannel($channel);

        $this->entityManager->flush();

        return $this->redirectToRoute("app_quality_channels_all", ['id' => $quality->getId()]);
    }

    #[Route("/disconnect/{channel}", name: 'app_quality_channels_disconnect', methods:['POST'])]
    public function disconnect(Quality $quality, Channel $channel): Response
    {
        $quality->removeChannel($channel);

        $this->entityManager->flush();

        return $this->redirectToRoute("app_quality_channels", ['id' => $quality->getId()]);
    }
}
