<?php

namespace App\Handlers;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

class AccessDeniedHandler extends AbstractController implements AccessDeniedHandlerInterface
{

  public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
  {
    if (!$this->getUser()) {
      return $this->redirectToRoute("app_login", [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('403.html.twig');
  }
}
