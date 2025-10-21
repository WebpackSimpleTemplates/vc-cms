<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\CreateUserType;
use App\Form\UserProfileType;
use App\Form\UserType;
use App\Repository\HistoryRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
final class UserController extends AbstractController
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
        private KernelInterface $kernel
    ) {}

    private function mapUserForm(FormInterface $form, User $user) {
        $plainPassword = $form->get('plainPassword')->getData();

        if ($plainPassword) {
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $plainPassword));
        }

        $avatarFile = ($form->get("avatar")->getData());

        if ($avatarFile) {
            /** @var UploadedFile $avatarFile */
            $filename = uniqid()."-".$avatarFile->getClientOriginalName();
            $user->setAvatar("/uploads"."/".$filename);

            $avatarFile->move($this->kernel->getProjectDir()."/public/uploads/", $filename);
        }
    }

    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository, PaginatorInterface $paginator, Request $request): Response
    {
        return $this->render('user/index.html.twig', [
            'pagination' => $paginator->paginate(
                $userRepository->getMany(),
                $request->query->getInt("page", 1),
                10,
            ),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        HistoryRepository $history,
    ): Response
    {
        $user = new User();
        $form = $this->createForm(CreateUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->mapUserForm($form, $user);

            $entityManager->persist($user);
            $entityManager->flush();

            $history->write("Создание пользователя", $user->getEmail());

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/profile', name: 'app_user_profile', methods: ['GET', 'POST'])]
    public function profile(
        Request $request,
        EntityManagerInterface $entityManager,
        HistoryRepository $history,
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $oldEmail = $user->getEmail();
        $form = $this->createForm(UserProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->mapUserForm($form, $user);

            $entityManager->flush();

            $history->write("Редактирование профиля пользователя", $oldEmail." => ".$user->getEmail());

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager,
        HistoryRepository $history,
        User $user,
    ): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $oldEmail = $user->getEmail();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->mapUserForm($form, $user);

            $entityManager->flush();

            $history->write("Редактирование пользователя", $oldEmail." => ".$user->getEmail());

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
            'is_self' => $currentUser->getId() === $user->getId(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_user_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        HistoryRepository $history,
    ): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if ($currentUser->getId() === $user->getId()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();

            $history->write("Удаление пользователя", $user->getEmail());
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
