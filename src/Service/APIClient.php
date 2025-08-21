<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class APIClient
{
    private string $baseUrl;
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient, string $baseUrl)
    {
        $this->httpClient = $httpClient;
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function fetchUsers(): array
    {
        $response = $this->httpClient->request('GET', $this->baseUrl . '/users');
        return $response->toArray();
    }
}

