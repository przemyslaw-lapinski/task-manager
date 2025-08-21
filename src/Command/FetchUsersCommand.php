<?php
namespace App\Command;

use App\Service\APIClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:fetch-users', description: 'Fetch users from API and output JSON')]
class FetchUsersCommand extends Command
{
    private APIClient $apiClient;

    public function __construct(APIClient $apiClient)
    {
        parent::__construct();
        $this->apiClient = $apiClient;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $users = $this->apiClient->fetchUsers();
        $output->writeln(json_encode($users, JSON_PRETTY_PRINT));
        return Command::SUCCESS;
    }
}

