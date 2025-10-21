<?php

namespace App\Repository;

use App\Entity\HistoryLog;
use App\Entity\User;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class HistoryRepository {
    public function __construct(
        private Security $security,
        private EntityManagerInterface $entityManager
    ) { }

    public function write(string $action, string $params = '', bool $isConsultant = false)
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if (!$user) {
            return;
        }

        $log = new HistoryLog();

        $log->setDatetime(new DateTime());
        $log->setUsr($user);
        $log->setIsConsultant($isConsultant);
        $log->setAction($action);
        $log->setParams($params);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
