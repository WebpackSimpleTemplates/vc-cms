<?php

namespace App\Controller;

use App\Entity\Channel;
use App\Entity\Quality;
use App\Repository\HistoryRepository;
use App\Repository\QualityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/manage/channel/{id}/qualities')]
final class ChannelQualitiesController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[Route("/connected", name: 'app_channel_qualities')]
    public function index(Channel $channel, QualityRepository $qualityRepository, PaginatorInterface $paginator, Request $request): Response
    {
        return $this->render('channel/qualities.html.twig', [
            'channel' => $channel,
            'pagination' => $paginator->paginate(
                $qualityRepository->getChannelQualitiesQuery($channel),
                $request->query->getInt("page", 1),
                10,
            ),
        ]);
    }

    #[Route("/all", name: 'app_channel_qualities_all')]
    public function all(Channel $channel, QualityRepository $qualityRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $pagination = $paginator->paginate(
            $qualityRepository->getNotMain(),
            $request->query->getInt("page", 1),
            10,
        );

        $currentItemsIds = array_map(fn(Quality $us) => $us->getId(), (array) $pagination->getItems());

        $connected = $qualityRepository
            ->getChannelQualitiesQuery($channel)
            ->andWhere("q.id IN(:ids)")
            ->setParameter("ids", $currentItemsIds)
            ->getQuery()
            ->getResult();

        return $this->render('channel/qualities-all.html.twig', [
            'channel' => $channel,
            'pagination' => $pagination,
            'connectedIds' => array_map(fn(Quality $us) => $us->getId(), (array) $connected),
        ]);
    }

    #[Route("/connect/{quality}", name: 'app_channel_qualities_connect', methods:['POST'])]
    public function connect(Channel $channel, Quality $quality, HistoryRepository $history): Response
    {
        $quality->addChannel($channel);

        $this->entityManager->flush();
        $history->writeConnecting($channel, $quality);

        return $this->redirectToRoute("app_channel_qualities_all", ['id' => $channel->getId()]);
    }

    #[Route("/disconnect/{quality}", name: 'app_channel_qualities_disconnect', methods:['POST'])]
    public function disconnect(Channel $channel, Quality $quality, HistoryRepository $history): Response
    {
        $quality->removeChannel($channel);

        $this->entityManager->flush();
        $history->writeDisconnecting($channel, $quality);

        return $this->redirectToRoute("app_channel_qualities", ['id' => $channel->getId()]);
    }
}
