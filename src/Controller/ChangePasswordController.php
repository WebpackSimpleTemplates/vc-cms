<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ChangePasswordController extends AbstractController
{
    #[Route('/manage/change/password', name: 'app_change_password')]
    public function index(): Response
    {
        return $this->render('change_password/index.html.twig', [
            'controller_name' => 'ChangePasswordController',
        ]);
    }
}
