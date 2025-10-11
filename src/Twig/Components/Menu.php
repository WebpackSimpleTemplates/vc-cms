<?php

namespace App\Twig\Components;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Menu
{
  public function __construct(
    private Security $security,
    private AccessDecisionManagerInterface $accessDecisionManager,
  ){}

  public function getUser() {
    return $this->security->getUser();
  }

  public function isAccess(string $role) {
    return $this->accessDecisionManager->decide($this->security->getToken(), [$role]);
  }
}
