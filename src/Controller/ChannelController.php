<?php

namespace App\Controller;

use App\Entity\Channel;
use App\Form\ChannelType;
use App\Form\ScheduleType;
use App\Repository\ChannelRepository;
use App\Repository\HistoryRepository;
use App\Repository\ScheduleRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/manage/channel')]
final class ChannelController extends AbstractController
{
    #[Route(name: 'app_channel_index', methods: ['GET'])]
    public function index(ChannelRepository $channelRepository, PaginatorInterface $paginator, Request $request): Response
    {
        return $this->render('channel/index.html.twig', [
            'pagination' => $paginator->paginate(
                $channelRepository->getMany(),
                $request->query->getInt("page", 1),
                10,
            ),
            'baseUrl' => $request->getBaseUrl(),
        ]);
    }

    #[Route('/new', name: 'app_channel_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        HistoryRepository $history,
    ): Response
    {
        $channel = new Channel();
        $form = $this->createForm(ChannelType::class, $channel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($channel);
            $entityManager->flush();

            $history->write("Создание канала", $channel->getTitle());

            return $this->redirectToRoute('app_channel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('channel/new.html.twig', [
            'channel' => $channel,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_channel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Channel $channel, HistoryRepository $history, EntityManagerInterface $entityManager): Response
    {
        if ($channel->getDeletedAt()) {
            return $this->render('channel/deleted.html.twig', [
                'channel' => $channel,
            ]);
        }

        $oldtitle = $channel->getTitle();

        $form = $this->createForm(ChannelType::class, $channel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $history->write("Редактирование канала", $oldtitle." => ".$channel->getTitle());

            return $this->redirectToRoute('app_channel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('channel/edit.html.twig', [
            'channel' => $channel,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_channel_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Channel $channel,
        HistoryRepository $history,
        EntityManagerInterface $entityManager,
    ): Response
    {
        if (!$channel->getDeletedAt() && $this->isCsrfTokenValid('delete'.$channel->getId(), $request->getPayload()->getString('_token'))) {
            $channel->setDeletedAt(new DateTime());
            $channel->setDeletedBy($this->getUser());
            $entityManager->flush();

            $history->write("Удаление канала", $channel->getTitle());
        }

        return $this->redirectToRoute('app_channel_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/schedule', name: 'app_channel_schedule', methods:['GET', 'POST'])]
    public function schedule(Request $request, Channel $channel, ScheduleRepository $repository, EntityManagerInterface $entityManager): Response
    {
        $schedule = $channel->getSchedule() ?? $repository->copyGeneral();
        $form = $this->createForm(ScheduleType::class, $schedule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $schedule->setChannel($channel);
            $channel->setSchedule($schedule);
            $entityManager->persist($schedule);
            $entityManager->flush();
            return $this->redirectToRoute('app_channel_schedule', ['id' => $channel->getId()]);
        }

        return $this->render('channel/schedule.html.twig', [
            'channel' => $channel,
            'time' => date('d.m.Y H:i:s'),
            'form' => $form,
        ]);
    }

    #[Route('/{id}/schedule-to-general', name: 'app_channel_schedule_to_general', methods:['GET', 'POST'])]
    public function scheduleToMain(Channel $channel, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($channel->getSchedule());
        $entityManager->flush();

        return $this->redirectToRoute('app_channel_schedule', ['id' => $channel->getId()]);
    }
}
