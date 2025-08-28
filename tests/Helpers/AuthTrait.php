<?php

namespace App\Tests\Helpers;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait AuthTrait
{
    private function getJwtToken(KernelBrowser $client, $email, $password = 'password')
    {
        $client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => $email,
            'password' => $password,
        ]));
        $data = json_decode($client->getResponse()->getContent(), true);
        return $data['token'] ?? null;
    }
}
