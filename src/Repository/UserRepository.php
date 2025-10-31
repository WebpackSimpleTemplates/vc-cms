<?php

namespace App\Repository;

use App\Entity\Channel;
use App\Entity\Quality;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function getMany() {
        $qb = $this->createQueryBuilder('u');

        $qb->orderBy('u.id', 'desc');

        return $qb;
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function getOperators() {
        $qb = $this->createQueryBuilder('u');

        $qb->orderBy('u.id', 'desc');
        $qb->where("u.isConsultant = true");

        return $qb;
    }

    public function getChannelUsersQuery(Channel $channel)
    {
        $qb = $this->getMany();

        $qb->join('u.channels', 'c');

        $qb->where("c.id = :cid");

        $qb->setParameter("cid", $channel->getId());

        return $qb;
    }

    public function getQualityUsersQuery(Quality $quality)
    {
        $qb = $this->getMany();

        $qb->join("u.qualities", 'q');

        $qb->where("q.id = :qid");

        $qb->setParameter("qid", $quality->getId());

        return $qb;
    }
}
