<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

final class MainController extends AbstractController
{
    public function __construct(
        private Security $security,
        private AccessDecisionManagerInterface $accessDecisionManager,
    ){}

    #[Route('/manage/', name: 'app_main')]
    public function index(): Response
    {
        if ($this->accessDecisionManager->decide($this->security->getToken(), ["ROLE_READER"])) {
            return $this->redirectToRoute("app_monitoring");
        }

        return $this->redirectToRoute("app_user_profile");
    }
}
