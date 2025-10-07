<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user/{id}/channels')]
final class UserChannelsController extends AbstractController
{
    #[Route(name: 'app_user_channels')]
    public function index(User $user): Response
    {
        return $this->render('user/channels.html.twig', [
            'controller_name' => 'UserChannelsController',
        ]);
    }
}
