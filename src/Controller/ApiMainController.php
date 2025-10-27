<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ApiMainController extends AbstractController
{
    #[Route('/api/test', name: 'app_api_test')]
    public function index(): JsonResponse
    {
        return $this->json(['message' => 'success']);
    }
}
