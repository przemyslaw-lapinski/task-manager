<?php

namespace App\Domain\Common\Event;

interface DomainEvent
{
    public function getOccurredAt(): \DateTimeInterface;

    public function toPayload(): array;

    public static function fromPayload(array $payload): self;
}
