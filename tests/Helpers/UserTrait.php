<?php

namespace App\Tests\Helpers;

use App\Infrastructure\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait UserTrait
{
    private function createUser(string $email, array $roles, KernelBrowser $client): User
    {
        $em = $client->getContainer()->get('doctrine')->getManager();
        $passwordHasher = $client->getContainer()->get('security.password_hasher');

        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setPassword($passwordHasher->hashPassword($user, 'password'));
        $em->persist($user);
        $em->flush();

        return $user;
    }
}
