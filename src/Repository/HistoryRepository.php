<?php

namespace App\Repository;

use App\Entity\Channel;
use App\Entity\HistoryLog;
use App\Entity\Quality;
use App\Entity\User;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
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

    private function getConnectingEntityLabel($obj)
    {
        if ($obj instanceof User) {
            return "консультант";
        }

        if ($obj instanceof Channel) {
            return "канал";
        }

        if ($obj instanceof Quality) {
            return "оценка качества";
        }
    }

    private function getItemsConnect($obj1, $obj2)
    {
        $label1 = $this->getConnectingEntityLabel($obj1);
        $label2 = $this->getConnectingEntityLabel($obj2);

        $item1 = [];
        $item2 = [];

        if (strcmp($label1, $label2)) {
            $item1 = [$label1, $obj1->getTitle()];
            $item2 = [$label2, $obj2->getTitle()];
        } else {
            $item2 = [$label1, $obj1->getTitle()];
            $item1 = [$label2, $obj2->getTitle()];
        }

        return [$item1, $item2];
    }

    public function writeConnecting($obj1, $obj2)
    {
        $items = $this->getItemsConnect($obj1, $obj2);

        $this->write("Подключение ".$items[0][0]." + ".$items[1][0], $items[0][1]." + ".$items[1][1]);
    }

    public function writeDisconnecting($obj1, $obj2)
    {
        $items = $this->getItemsConnect($obj1, $obj2);

        $this->write("Отключение ".$items[0][0]." - ".$items[1][0], $items[0][1]." - ".$items[1][1]);
    }
}
