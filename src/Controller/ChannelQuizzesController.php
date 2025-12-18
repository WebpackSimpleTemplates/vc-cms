<?php

namespace App\Controller;

use App\Entity\Channel;
use App\Entity\Quiz;
use App\Repository\HistoryRepository;
use App\Repository\QuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/manage/channel/{id}/quizzes')]
final class ChannelQuizzesController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[Route("/connected", name: 'app_channel_quizzes')]
    public function index(Channel $channel, QuizRepository $quizRepository, PaginatorInterface $paginator, Request $request): Response
    {
        return $this->render('channel/quizzes.html.twig', [
            'channel' => $channel,
            'pagination' => $paginator->paginate(
                $quizRepository->getChannelQuizzesQuery($channel),
                $request->query->getInt("page", 1),
                10,
            ),
        ]);
    }

    #[Route("/all", name: 'app_channel_quizzes_all')]
    public function all(Channel $channel, QuizRepository $quizRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $pagination = $paginator->paginate(
            $quizRepository->getNotMain(),
            $request->query->getInt("page", 1),
            10,
        );

        $currentItemsIds = array_map(fn(Quiz $us) => $us->getId(), (array) $pagination->getItems());

        $connected = $quizRepository
            ->getChannelQuizzesQuery($channel)
            ->andWhere("q.id IN(:ids)")
            ->setParameter("ids", $currentItemsIds)
            ->getQuery()
            ->getResult();

        return $this->render('channel/quizzes-all.html.twig', [
            'channel' => $channel,
            'pagination' => $pagination,
            'connectedIds' => array_map(fn(Quiz $us) => $us->getId(), (array) $connected),
        ]);
    }

    #[Route("/connect/{quiz}", name: 'app_channel_quizzes_connect', methods:['POST'])]
    public function connect(Channel $channel, Quiz $quiz, HistoryRepository $history): Response
    {
        $quiz->addChannel($channel);

        $this->entityManager->flush();
        $history->writeConnecting($channel, $quiz);

        return $this->redirectToRoute("app_channel_quizzes_all", ['id' => $channel->getId()]);
    }

    #[Route("/disconnect/{quiz}", name: 'app_channel_quizzes_disconnect', methods:['POST'])]
    public function disconnect(Channel $channel, Quiz $quiz, HistoryRepository $history): Response
    {
        $quiz->removeChannel($channel);

        $this->entityManager->flush();
        $history->writeDisconnecting($channel, $quiz);

        return $this->redirectToRoute("app_channel_quizzes", ['id' => $channel->getId()]);
    }
}
