<?php

namespace App\Controller;

use App\Entity\Quality;
use App\Entity\User;
use App\Repository\HistoryRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/manage/quality/{id}/users')]
final class QualityUsersController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[Route("/connected", name: 'app_quality_users')]
    public function index(Quality $quality, UserRepository $userRepository, PaginatorInterface $paginator, Request $request): Response
    {
        return $this->render('quality/users.html.twig', [
            'quality' => $quality,
            'pagination' => $paginator->paginate(
                $userRepository->getQualityUsersQuery($quality),
                $request->query->getInt("page", 1),
                10,
            ),
        ]);
    }

    #[Route("/all", name: 'app_quality_users_all')]
    public function all(Quality $quality, UserRepository $userRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $pagination = $paginator->paginate(
            $userRepository->getOperators(),
            $request->query->getInt("page", 1),
            10,
        );

        $currentItemsIds = array_map(fn(User $us) => $us->getId(), (array) $pagination->getItems());

        $connected = $userRepository
            ->getQualityUsersQuery($quality)
            ->andWhere("u.id IN(:ids)")
            ->setParameter("ids", $currentItemsIds)
            ->getQuery()
            ->getResult();

        return $this->render('quality/users-all.html.twig', [
            'quality' => $quality,
            'pagination' => $pagination,
            'connectedIds' => array_map(fn(User $us) => $us->getId(), (array) $connected),
        ]);
    }

    #[Route("/connect/{user}", name: 'app_quality_users_connect', methods:['POST'])]
    public function connect(Quality $quality, User $user, HistoryRepository $history): Response
    {
        $quality->addConsultant($user);

        $this->entityManager->flush();
        $history->writeConnecting($user, $quality);

        return $this->redirectToRoute("app_quality_users_all", ['id' => $quality->getId()]);
    }

    #[Route("/disconnect/{user}", name: 'app_quality_users_disconnect', methods:['POST'])]
    public function disconnect(Quality $quality, User $user, HistoryRepository $history): Response
    {
        $quality->removeConsultant($user);

        $this->entityManager->flush();
        $history->writeDisconnecting($user, $quality);

        return $this->redirectToRoute("app_quality_users", ['id' => $quality->getId()]);
    }
}
