<?php

namespace App\Infrastructure\Repository;

use App\Infrastructure\Entity\TaskView;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<TaskView>
 */
class TaskViewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskView::class);
    }

    /** @return TaskView[] */
    public function findByAssignee(Uuid $userId): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.assignedUserId = :u')
            ->setParameter('u', $userId, 'uuid')
            ->orderBy('t.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return TaskView[] */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
