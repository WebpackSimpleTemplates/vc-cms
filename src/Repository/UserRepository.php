<?php

namespace App\Repository;

use App\Entity\Channel;
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
        $users = $this->findAll();

        return array_filter($users, fn(User $user) => $user->isOperator());
    }

    public function getChannelUsersQuery(Channel $channel)
    {
        $qb = $this->createQueryBuilder('u');

        $qb->orderBy('u.id', 'desc');

        $qb->join('u.channels', 'c');

        $qb->where("c.id = :cid");

        $qb->setParameter("cid", $channel->getId());

        return $qb;
    }
}
