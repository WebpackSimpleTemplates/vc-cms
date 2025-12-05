<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:set-root',
    description: 'Добавить суперпользователя',
)]
class SetRootCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $userPasswordHasher
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email')
            ->addArgument('password', InputArgument::REQUIRED, 'Password')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument("email");
        $password = $input->getArgument("password");

        $user = $this->userRepository->findOneBy(["email" => $email]);

        if ($user) {
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $password));
            $this->entityManager->flush();

            $io->success('Пароль суперпользователя '.$email.' обновлён');

            return Command::SUCCESS;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setAvatar("/default-avatar.jpg");
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $password));
        $user->setRoles(["ROLE_ROOT"]);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('Суперпользователь '.$email.' создан');

        return Command::SUCCESS;
    }
}
