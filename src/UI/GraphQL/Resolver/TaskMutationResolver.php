<?php

namespace App\UI\GraphQL\Resolver;

use App\Application\Task\Command\ChangeStatusCommand;
use App\Application\Task\Command\CreateTaskCommand;
use Symfony\Component\Messenger\MessageBusInterface;

class TaskMutationResolver
{
    public function __construct(
        private MessageBusInterface $bus
    ) {}

    public function createTask(\Overblog\GraphQLBundle\Definition\Argument $args): string
    {
       $args = $args->getArrayCopy();

        $this->bus->dispatch(new CreateTaskCommand(
            $args['title'],
            $args['description'],
            $args['assignedUserId'],
        ));

        return true;
    }

    public function changeStatus(\Overblog\GraphQLBundle\Definition\Argument $args): bool
    {
        $args = $args->getArrayCopy();

        $this->bus->dispatch(new ChangeStatusCommand(
            $args['taskId'],
            $args['toStatus']
        ));

        return true;
    }
}
