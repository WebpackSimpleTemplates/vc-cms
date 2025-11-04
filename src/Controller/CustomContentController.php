<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CustomContentController extends AbstractController
{
    #[Route('/custom/content', name: 'app_custom_content')]
    public function index(): Response
    {
        return $this->render('custom_content/index.html.twig', [
            'controller_name' => 'CustomContentController',
        ]);
    }
}
