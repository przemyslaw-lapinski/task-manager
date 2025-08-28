<?php

namespace App\UI\GraphQL\Resolver;

use App\Infrastructure\Entity\TaskView;
use App\Infrastructure\EventStore\DoctrineEventStore;
use App\Infrastructure\Repository\TaskViewRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Uid\Uuid;

class TaskQueryResolver
{
    public function __construct(
        private readonly TaskViewRepository $repo,
        private readonly DoctrineEventStore $eventStore,
        private readonly Security $security
    ) {}

    /** @return array<int, array<string, mixed>> */
    public function myTasks(): array
    {
        $userId = (string)$this->security->getUser()->getId();
        $rows = $this->repo->findByAssignee(Uuid::fromString($userId));
        return array_map([$this, 'toTaskGql'], $rows);
    }

    /** @return array<int, array<string, mixed>> */
    public function allTasks(): array
    {
        $user = $this->security->getUser();
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            throw new \RuntimeException('Forbidden');
        }

        $rows = $this->repo->findAllOrdered();
        return array_map([$this, 'toTaskGql'], $rows);
    }

    /** @return array<int, array<string, mixed>> */
    public function taskHistory(string $taskId): array
    {
        $out = [];
        foreach ($this->eventStore->stream(\App\Domain\Task\ValueObject\TaskId::fromString($taskId)) as $rec) {
            $out[] = [
                'event'      => $rec->getEventName(),
                'payload'    => json_encode($rec->getPayload(), JSON_UNESCAPED_UNICODE),
                'recordedAt' => ($rec->getCreatedAt() ?? new \DateTimeImmutable('@0'))->format(\DateTimeInterface::ATOM),
            ];
        }
        return $out;
    }

    /** @return array<string, mixed> */
    private function toTaskGql(TaskView $t): array
    {
        return [
            'id'             => $t->getId()->toRfc4122(),
            'title'           => $t->getTitle(),
            'description'    => $t->getDescription(),
            'status'         => $t->getStatus(),
            'assignedUserId' => $t->getAssignedUserId()->toRfc4122(),
            'updatedAt'      => $t->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }
}
