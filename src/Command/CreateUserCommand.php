<?php

namespace App\Command;

use App\Entity\User;
use App\Util\EmailValidatorUtil;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Create user.',
    hidden: true
)]
class CreateUserCommand extends Command
{

    private UserPasswordHasherInterface $userPasswordHasher;
    private EntityManagerInterface $entityManager;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager)
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'E-mail address')
            ->addArgument('password', InputArgument::REQUIRED, 'Password')
            ->addArgument('admin', InputArgument::REQUIRED, 'Whether the user has administrator privileges? y/n')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $admin = $input->getArgument('admin');

        $isAdmin = (strtolower($admin) === 'y' || strtolower($admin) === 'yes');
        $correctEmail = EmailValidatorUtil::validateEmail($email);

        if (!$correctEmail) {
            $io->error('Podany adres email nie jest prawidłowy');
            return Command::FAILURE;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $password));

        if($isAdmin) {
            $user->setRoles(['ROLE_ADMIN']);
        }

        try {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }catch (\Exception $exception) {
            $io->error('Nie udalo sie stworzyc uzytkownika sprobuj ponownie pozniej!');
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }

       $io->success('Uzytkownik ' . $email . ' zostal stworzony' . ($isAdmin ? ' jako administrator' : '') . '!');
        return Command::SUCCESS;
    }
}
