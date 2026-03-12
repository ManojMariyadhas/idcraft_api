<?php

namespace App\Command;

use App\Entity\Admin;
use App\Repository\AdminRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Create an admin user'
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AdminRepository $adminRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::OPTIONAL, 'Admin username')
            ->addArgument('password', InputArgument::OPTIONAL, 'Admin password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $username = (string) ($input->getArgument('username') ?? '');
        if ($username === '') {
            $username = $io->ask('Username');
        }

        if ($username === '') {
            $io->error('Username is required.');
            return Command::FAILURE;
        }

        if ($this->adminRepository->findOneBy(['username' => $username])) {
            $io->error('Admin username already exists.');
            return Command::FAILURE;
        }

        $password = (string) ($input->getArgument('password') ?? '');
        if ($password === '') {
            $password = $io->askHidden('Password');
        }

        if ($password === '') {
            $io->error('Password is required.');
            return Command::FAILURE;
        }

        $admin = (new Admin())->setUsername($username);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, $password));

        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $io->success('Admin created successfully.');

        return Command::SUCCESS;
    }
}
