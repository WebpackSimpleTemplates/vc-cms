<?php

namespace App\Repository;

use App\Entity\CustomContent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CustomContent>
 */
class CustomContentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomContent::class);
    }

    public function getCurrent()
    {
        $rows = $this->findAll();


        return count($rows) > 0 ? $rows[0] : new CustomContent();
    }
}
