<?php

namespace App\Tests\Helpers;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait GraphQLTrait
{
    private function sendGraphQL(KernelBrowser $client, string $query,  string $authToken, array $variables = []): array
    {
        $client->request(
            'POST',
            '/graphql',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_Authorization' => 'Bearer ' . $authToken,],
            json_encode(['query' => $query, 'variables' => $variables], JSON_THROW_ON_ERROR)
        );

        self::assertSame(200, $client->getResponse()->getStatusCode(), (string)$client->getResponse()->getContent());
        $json = json_decode((string)$client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        return $json;
    }
}
