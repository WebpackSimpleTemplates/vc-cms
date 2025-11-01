<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Quality;
use App\Repository\HistoryRepository;
use App\Repository\QualityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/manage/user/{id}/qualities')]
final class UserQualitiesController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[Route("/manage/connected", name: 'app_user_qualities')]
    public function index(User $user, QualityRepository $qualityRepository, PaginatorInterface $paginator, Request $request): Response
    {
        return $this->render('user/qualities.html.twig', [
            'user' => $user,
            'pagination' => $paginator->paginate(
                $qualityRepository->getUserQualitiesQuery($user),
                $request->query->getInt("page", 1),
                10,
            ),
        ]);
    }

    #[Route("/manage/all", name: 'app_user_qualities_all')]
    public function all(User $user, QualityRepository $qualityRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $pagination = $paginator->paginate(
            $qualityRepository->getNotMain(),
            $request->query->getInt("page", 1),
            10,
        );

        $currentItemsIds = array_map(fn(Quality $us) => $us->getId(), (array) $pagination->getItems());

        $connected = $qualityRepository
            ->getUserQualitiesQuery($user)
            ->andWhere("q.id IN(:ids)")
            ->setParameter("ids", $currentItemsIds)
            ->getQuery()
            ->getResult();

        return $this->render('user/qualities-all.html.twig', [
            'user' => $user,
            'pagination' => $pagination,
            'connectedIds' => array_map(fn(Quality $us) => $us->getId(), (array) $connected),
        ]);
    }

    #[Route("/manage/connect/{quality}", name: 'app_user_qualities_connect', methods:['POST'])]
    public function connect(User $user, Quality $quality, HistoryRepository $history): Response
    {
        $quality->addConsultant($user);

        $this->entityManager->flush();
        $history->writeConnecting($user, $quality);

        return $this->redirectToRoute("app_user_qualities_all", ['id' => $user->getId()]);
    }

    #[Route("/manage/disconnect/{quality}", name: 'app_user_qualities_disconnect', methods:['POST'])]
    public function disconnect(User $user, Quality $quality, HistoryRepository $history): Response
    {
        $quality->removeConsultant($user);

        $this->entityManager->flush();
        $history->writeDisconnecting($user, $quality);

        return $this->redirectToRoute("app_user_qualities", ['id' => $user->getId()]);
    }
}
