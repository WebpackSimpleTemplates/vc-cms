<?php

namespace App\Twig\Components;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Menu
{  
  public function __construct(
    private Security $security
  ){}

  public function getUser() {
    return $this->security->getUser();
  }

  public function hasRole(string $role) {
    return in_array($role, $this->security->getUser()->getRoles());
  }
}
