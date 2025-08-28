<?php

namespace App\Infrastructure\EventStore;

use App\Domain\Common\Event\DomainEvent;
use App\Domain\Task\Model\Task;
use App\Domain\Task\Repository\TaskEventSourcedRepositoryInterface;
use App\Domain\Task\ValueObject\TaskId;
use App\Infrastructure\Entity\Event;
use App\Infrastructure\EventStore\Support\EventNameResolver;
use App\Infrastructure\Projection\TaskProjector;
use App\Infrastructure\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class DoctrineEventStore implements TaskEventSourcedRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private EventRepository $events,
        private EventNameResolver $nameResolver,
        private TaskProjector $projector,
        private string $aggregateType = 'task'
    ) {}

    public function load(TaskId $id): Task
    {
        /** @var Event[] $records */
        $records = $this->events->createQueryBuilder('e')
            ->where('e.aggregateId = :id')
            ->andWhere('e.aggregateType = :type')
            ->orderBy('e.version', 'ASC')
            ->setParameter('id', Uuid::fromString((string) $id))
            ->setParameter('type', $this->aggregateType)
            ->getQuery()
            ->getResult();

        if (!$records) {
            throw new \RuntimeException(sprintf('No events for aggregate %s:%s', $this->aggregateType, $id));
        }

        $domainEvents = [];
        foreach ($records as $rec) {
            $fqcn = $this->nameResolver->toFqcn($rec->getEventName());
            /** @var class-string<DomainEvent> $fqcn */
            $domainEvents[] = $fqcn::fromPayload(
                $rec->getPayload(),
            );
        }

        return Task::reconstitute($domainEvents);
    }

    public function save(Task $task): void
    {
        $uncommitted = $task->pullUncommittedEvents();
        if (!$uncommitted) {
            return;
        }

        $conn = $this->em->getConnection();
        $conn->beginTransaction();

        try {
            $aggregateId = Uuid::fromString($task->getId());

            $lastVersion = (int) $conn->fetchOne(
                'SELECT COALESCE(MAX(version), 0) FROM event WHERE aggregate_id = :id AND aggregate_type = :type',
                ['id' => $aggregateId, 'type' => $this->aggregateType]
            );

            $version = $lastVersion;
            foreach ($uncommitted as $event) {
                if (!$event instanceof DomainEvent) {
                    throw new \RuntimeException('All events must implement DomainEvent');
                }

                $record = (new Event())
                    ->setAggregateId($aggregateId)
                    ->setAggregateType($this->aggregateType)
                    ->setVersion(++$version)
                    ->setEventName($this->nameResolver->toName($event::class))
                    ->setPayload($event->toPayload())
                    ->setCreatedAt($event->getOccurredAt());

                $this->em->persist($record);
            }

            $this->em->flush();

            foreach ($uncommitted as $event) {
                $this->projector->project($event);
            }

            $conn->commit();
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            $conn->rollBack();
            throw new \RuntimeException('Optimistic concurrency conflict while saving events', 0, $e);
        } catch (\Throwable $e) {
            $conn->rollBack();
            throw $e;
        }
    }
}
