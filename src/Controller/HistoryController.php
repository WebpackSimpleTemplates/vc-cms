<?php

namespace App\Controller;

use App\Repository\HistoryLogRepository;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/history')]
final class HistoryController extends AbstractController
{
    #[Route('/', name: 'app_history')]
    public function index(
        Request $request,
        PaginatorInterface $paginator,
        HistoryLogRepository $history,
        UserRepository $userRepository,
    ): Response
    {
        $start = $request->query->get("start", "");
        $end = $request->query->get("end", "");
        $user = $request->query->get("user", "");
        $role = $request->query->get("role", "");
        $action = $request->query->get("action", "");

        return $this->render('history/index.html.twig', [
            'start' => $start,
            'end' => $end,
            'user' => $user,
            'role' => $role,
            'action' => $action,
            'actions' => $history->getActions(),
            'users' => $userRepository->getMany()->getQuery()->getResult(),
            'pagination' => $paginator->paginate(
                $history->getMany($start, $end, $user, $role, $action),
                $request->query->get("page", 1),
                10
            ),
        ]);
    }
}
