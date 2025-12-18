<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Quiz;
use App\Repository\HistoryRepository;
use App\Repository\QuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/manage/user/{id}/quizzes')]
final class UserQuizzesController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[Route("/connected", name: 'app_user_quizzes')]
    public function index(User $user, QuizRepository $quizRepository, PaginatorInterface $paginator, Request $request): Response
    {
        return $this->render('user/quizzes.html.twig', [
            'user' => $user,
            'pagination' => $paginator->paginate(
                $quizRepository->getUserQuizzesQuery($user),
                $request->query->getInt("page", 1),
                10,
            ),
        ]);
    }

    #[Route("/all", name: 'app_user_quizzes_all')]
    public function all(User $user, QuizRepository $quizRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $pagination = $paginator->paginate(
            $quizRepository->getNotMain(),
            $request->query->getInt("page", 1),
            10,
        );

        $currentItemsIds = array_map(fn(Quiz $us) => $us->getId(), (array) $pagination->getItems());

        $connected = $quizRepository
            ->getUserQuizzesQuery($user)
            ->andWhere("q.id IN(:ids)")
            ->setParameter("ids", $currentItemsIds)
            ->getQuery()
            ->getResult();

        return $this->render('user/quizzes-all.html.twig', [
            'user' => $user,
            'pagination' => $pagination,
            'connectedIds' => array_map(fn(Quiz $us) => $us->getId(), (array) $connected),
        ]);
    }

    #[Route("/connect/{quiz}", name: 'app_user_quizzes_connect', methods:['POST'])]
    public function connect(User $user, Quiz $quiz, HistoryRepository $history): Response
    {
        $quiz->addConsultant($user);

        $this->entityManager->flush();
        $history->writeConnecting($user, $quiz);

        return $this->redirectToRoute("app_user_quizzes_all", ['id' => $user->getId()]);
    }

    #[Route("/disconnect/{quiz}", name: 'app_user_quizzes_disconnect', methods:['POST'])]
    public function disconnect(User $user, Quiz $quiz, HistoryRepository $history): Response
    {
        $quiz->removeConsultant($user);

        $this->entityManager->flush();
        $history->writeDisconnecting($user, $quiz);

        return $this->redirectToRoute("app_user_quizzes", ['id' => $user->getId()]);
    }
}
