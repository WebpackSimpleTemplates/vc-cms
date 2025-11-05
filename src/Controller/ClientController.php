<?php

namespace App\Controller;

use App\Repository\ChannelRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ClientController extends AbstractController
{
    #[Route('/', name: 'app_root')]
    public function index(ChannelRepository $repository, Security $security): Response
    {
        if ($security->getUser()) {
            return $this->redirectToRoute('app_main');
        }

        return $this->render('client/index.html.twig', [
            'channels' => $repository->findAll(),
        ]);
    }

    #[Route('/call/{id}', name: 'app_client')]
    public function channel(ChannelRepository $repository, ?string $id = null): Response
    {
        $channel = null;

        try {
            $channel = $repository->findOneBy(["id" => (int) $id]);
        } catch (\Throwable $th) {
            //throw $th;
        }

        return $this->render('client/channel.html.twig', [
            'channel' => $channel,
        ]);
    }
}
