<?php

namespace App\Infrastructure\Projection;

use App\Domain\Task\Event\TaskCreated;
use App\Domain\Task\Event\TaskStatusUpdated;
use App\Infrastructure\Entity\TaskView;
use App\Infrastructure\Repository\TaskViewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class TaskProjector
{
    public function __construct(
        private EntityManagerInterface $em,
        private TaskViewRepository $repo
    ) {}

    public function project(object $event): void
    {
        if ($event instanceof TaskCreated) {
            $this->onTaskCreated($event);
        } elseif ($event instanceof TaskStatusUpdated) {
            $this->onTaskStatusUpdated($event);
        }
    }

    private function onTaskCreated(TaskCreated $e): void
    {
        $view = new TaskView(
            Uuid::fromString($e->getTaskId()),
            $e->getTitle(),
            $e->getDescription(),
            $e->getStatus()->value,
            Uuid::fromString($e->getAssignedUserId()),
            new \DateTimeImmutable('now')
        );
        $this->em->persist($view);
        $this->em->flush();
    }

    private function onTaskStatusUpdated(TaskStatusUpdated $e): void
    {
        /** @var TaskView|null $view */
        $view = $this->repo->find(Uuid::fromString($e->getTaskId()));
        if (!$view) {
            return;
        }

        $view->setStatus($e->getTo()->value);
        $view->setUpdatedAt(new \DateTimeImmutable('now'));
        $this->em->flush();
    }
}
