<?php

namespace App\GraphQL\Resolver;

final class QueryResolver
{
    public static function hello(): string
    {
        return 'Hello from GraphQL';
    }
}
