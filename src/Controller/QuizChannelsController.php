<?php

namespace App\Controller;

use App\Entity\Channel;
use App\Entity\Quiz;
use App\Repository\ChannelRepository;
use App\Repository\HistoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/manage/quiz/{id}/channels')]
final class QuizChannelsController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[Route("/connected", name: 'app_quiz_channels')]
    public function index(Quiz $quiz, ChannelRepository $channelRepository, PaginatorInterface $paginator, Request $request): Response
    {
        return $this->render('quiz/channels.html.twig', [
            'quiz' => $quiz,
            'pagination' => $paginator->paginate(
                $channelRepository->getQuizChannelsQuery($quiz),
                $request->query->getInt("page", 1),
                10,
            ),
        ]);
    }

    #[Route("/all", name: 'app_quiz_channels_all')]
    public function all(Quiz $quiz, ChannelRepository $channelRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $pagination = $paginator->paginate(
            $channelRepository->getMany(),
            $request->query->getInt("page", 1),
            10,
        );

        $currentItemsIds = array_map(fn(Channel $ch) => $ch->getId(), (array) $pagination->getItems());

        $connected = $channelRepository
            ->getQuizChannelsQuery($quiz)
            ->andWhere("c.id IN(:ids)")
            ->setParameter("ids", $currentItemsIds)
            ->getQuery()
            ->getResult();

        return $this->render('quiz/channels-all.html.twig', [
            'quiz' => $quiz,
            'pagination' => $pagination,
            'connectedIds' => array_map(fn(Channel $ch) => $ch->getId(), (array) $connected),
        ]);
    }

    #[Route("/connect/{channel}", name: 'app_quiz_channels_connect', methods:['POST'])]
    public function connect(Quiz $quiz, Channel $channel, HistoryRepository $history): Response
    {
        $quiz->addChannel($channel);

        $this->entityManager->flush();
        $history->writeConnecting($channel, $quiz);

        return $this->redirectToRoute("app_quiz_channels_all", ['id' => $quiz->getId()]);
    }

    #[Route("/disconnect/{channel}", name: 'app_quiz_channels_disconnect', methods:['POST'])]
    public function disconnect(Quiz $quiz, Channel $channel, HistoryRepository $history): Response
    {
        $quiz->removeChannel($channel);

        $this->entityManager->flush();
        $history->writeDisconnecting($channel, $quiz);

        return $this->redirectToRoute("app_quiz_channels", ['id' => $quiz->getId()]);
    }
}
