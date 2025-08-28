<?php
namespace App\UI\Command;

use App\Infrastructure\Entity\User;
use App\Infrastructure\Service\APIClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:fetch-users', description: 'Fetch users from API and output JSON')]
class FetchUsersCommand extends Command
{
    public function __construct(
        private readonly APIClient $apiClient,
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $usersData = $this->apiClient->fetchUsers();
        $userRepo = $this->em->getRepository(User::class);

        foreach ($usersData as $data) {
            if ($userRepo->findOneBy(['email' => $data['email']])) {
                continue; // Skip if user exists
            }

            $user = new User();
            $user->setEmail($data['email']);
            $hashedPassword = $this->passwordHasher->hashPassword($user, 'secret123');
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_USER']);
            $user->setDetails($data);

            $this->em->persist($user);
        }
        $this->em->flush();

        $output->writeln('Users imported and persisted.');
        return Command::SUCCESS;
    }
}

