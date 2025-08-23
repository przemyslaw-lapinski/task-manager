<?php

namespace App\GraphQL\Resolver;

final class MutationResolver
{
    public static function ping(): string
    {
        return 'pong';
    }

    public function reverse(string $input): string
    {
        return strrev($input);
    }
}
