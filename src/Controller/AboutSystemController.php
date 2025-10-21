<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AboutSystemController extends AbstractController
{
    #[Route('/about/system', name: 'app_about_system')]
    public function index(): Response
    {
        return $this->render('about_system/index.html.twig', [
            'controller_name' => 'AboutSystemController',
        ]);
    }
}
