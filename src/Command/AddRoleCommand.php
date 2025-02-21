<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
  name: 'user:add:role'
)]
class AddRoleCommand extends Command
{
  
  /**
   * User manager.
   *
   * @var EntityManagerInterface
   */
  private EntityManagerInterface $em;
  
  /**
   * UserAddRole constructor.
   */
  public function __construct(EntityManagerInterface $em)
  {
    parent::__construct();
    $this->em = $em;
  }
  
  protected function configure(): void
  {
    $this
      ->addArgument('email', InputArgument::OPTIONAL, 'Email of user to add the role')
      ->addArgument('role', InputArgument::OPTIONAL, 'Role to add to user')
      ->setHelp(<<<'EOT'
      The <info>user:add:role</info> command adds a role to a user
      
      <info>php %command.full_name% matthieu ROLE_CUSTOM</info>
      EOT);
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io     = new SymfonyStyle($input, $output);
    $helper = $this->getHelper('question');
  
    if (!$input->getArgument('email')) {
      $unq = new Question('<question>Email of the user:</question> ');
      $unq->setValidator(function ($iun) {
        if (empty($iun)) {
          throw new Exception('Email can not be empty');
        }
        return $iun;
      });
      $iun = $helper->ask($input, $output, $unq);
      $input->setArgument('email', $iun);
    }
  
    $email = $input->getArgument('email');
  
    if (!$input->getArgument('role')) {
      $io->writeln('');
      $rq = new Question('<question>Please, enter a role (ej: ROLE_ADMIN):</question> ');
      $rq->setValidator(function ($ir) {
        if (empty($ir)) {
          throw new Exception('Role can not be empty');
        }
        return $ir;
      });
      $ir = $helper->ask($input, $output, $rq);
      $input->setArgument('role', $ir);
    }
  
    $role = $input->getArgument('role');
  
    if (!$email || !$role) {
      $io->error('Email and role are required');
      return Command::FAILURE;
    }
  
    $io->writeln('');
    $io->caution('This action will update the user roles in the database');
    $io->info(sprintf('User to add role: %s', $email));
    $io->info(sprintf('Role to add: %s', $role));
    
    $cq = new ConfirmationQuestion(
      '<error>Do you want to continue (y/n)?</error> ',
      false,
      '/^y/i'
    );
  
    if (!$helper->ask($input, $output, $cq)) {
      $io->writeln('');
      $io->error('Canceled action by user');
      return Command::FAILURE;
    }
    
    $user = $this->em->getRepository('App\Entity\User')->findOneByEmail($email);
    
    if (!$user) {
      $io->writeln('');
      $io->error('User not found');
      return Command::INVALID;
    }
    
    $user_roles = $user->getRoles();
  
    if (in_array($role, $user_roles)) {
      $io->writeln('');
      $io->error('User already has this role');
      return Command::INVALID;
    }
    
    $user_roles[] = $role;
  
    try {
      $user->setRoles($user_roles);
      $this->em->persist($user);
      $this->em->flush();
    } catch (Exception) {
      $io->writeln('');
      $io->error('Database update failed');
      return Command::FAILURE;
    }
    
    $io->writeln('');
    $io->info($role.' added to '.$email);
    $io->success('Database update success Madafaka!');

    return Command::SUCCESS;
  }
}
