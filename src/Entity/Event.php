<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'uuid', nullable: true)]
    private ?Uuid $aggregate_id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $aggregateType = null;

    #[ORM\Column]
    private ?int $version = null;

    #[ORM\Column(length: 255)]
    private ?string $eventName = null;

    #[ORM\Column]
    private array $payload = [];

    #[ORM\Column]
    private array $metadata = [];

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAggregateId(): ?Uuid
    {
        return $this->aggregate_id;
    }

    public function setAggregateId(?Uuid $aggregate_id): static
    {
        $this->aggregate_id = $aggregate_id;

        return $this;
    }

    public function getAggregateType(): ?string
    {
        return $this->aggregateType;
    }

    public function setAggregateType(?string $aggregateType): static
    {
        $this->aggregateType = $aggregateType;

        return $this;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function setVersion(int $version): static
    {
        $this->version = $version;

        return $this;
    }

    public function getEventName(): ?string
    {
        return $this->eventName;
    }

    public function setEventName(string $eventName): static
    {
        $this->eventName = $eventName;

        return $this;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function setPayload(array $payload): static
    {
        $this->payload = $payload;

        return $this;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(array $metadata): static
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
