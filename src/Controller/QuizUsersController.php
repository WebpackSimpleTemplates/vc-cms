<?php

namespace App\Controller;

use App\Entity\Quiz;
use App\Entity\User;
use App\Repository\HistoryRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/manage/quiz/{id}/users')]
final class QuizUsersController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[Route("/connected", name: 'app_quiz_users')]
    public function index(Quiz $quiz, UserRepository $userRepository, PaginatorInterface $paginator, Request $request): Response
    {
        return $this->render('quiz/users.html.twig', [
            'quiz' => $quiz,
            'pagination' => $paginator->paginate(
                $userRepository->getQuizUsersQuery($quiz),
                $request->query->getInt("page", 1),
                10,
            ),
        ]);
    }

    #[Route("/all", name: 'app_quiz_users_all')]
    public function all(Quiz $quiz, UserRepository $userRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $pagination = $paginator->paginate(
            $userRepository->getOperators(),
            $request->query->getInt("page", 1),
            10,
        );

        $currentItemsIds = array_map(fn(User $us) => $us->getId(), (array) $pagination->getItems());

        $connected = $userRepository
            ->getQuizUsersQuery($quiz)
            ->andWhere("u.id IN(:ids)")
            ->setParameter("ids", $currentItemsIds)
            ->getQuery()
            ->getResult();

        return $this->render('quiz/users-all.html.twig', [
            'quiz' => $quiz,
            'pagination' => $pagination,
            'connectedIds' => array_map(fn(User $us) => $us->getId(), (array) $connected),
        ]);
    }

    #[Route("/connect/{user}", name: 'app_quiz_users_connect', methods:['POST'])]
    public function connect(Quiz $quiz, User $user, HistoryRepository $history): Response
    {
        $quiz->addConsultant($user);

        $this->entityManager->flush();
        $history->writeConnecting($user, $quiz);

        return $this->redirectToRoute("app_quiz_users_all", ['id' => $quiz->getId()]);
    }

    #[Route("/disconnect/{user}", name: 'app_quiz_users_disconnect', methods:['POST'])]
    public function disconnect(Quiz $quiz, User $user, HistoryRepository $history): Response
    {
        $quiz->removeConsultant($user);

        $this->entityManager->flush();
        $history->writeDisconnecting($user, $quiz);

        return $this->redirectToRoute("app_quiz_users", ['id' => $quiz->getId()]);
    }
}
