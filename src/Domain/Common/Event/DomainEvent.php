<?php

namespace App\Domain\Common\Event;

interface DomainEvent
{
    public function getOccurredAt(): \DateTimeInterface;
}
