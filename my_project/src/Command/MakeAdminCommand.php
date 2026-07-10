<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:make-admin')]
class MakeAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'Email пользователя');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        if (!$user) {
            $output->writeln('<error>Пользователь не найден</error>');
            return Command::FAILURE;
        }

        $user->setRoles(['ROLE_ADMIN']);
        $this->entityManager->flush();

        $output->writeln('Пользователь стал администратором!');
        return Command::SUCCESS;
    }
}
