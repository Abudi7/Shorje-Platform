<?php

namespace App\Command;

use App\Service\EmailService;
use App\Entity\User;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:test-password-reset',
    description: 'Test password reset email functionality',
)]
class TestPasswordResetCommand extends Command
{
    public function __construct(
        private EmailService $emailService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'Email address to send test password reset email to');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        
        $output->writeln("Testing password reset email to: {$email}");
        
        try {
            // Create a mock user
            $user = new User();
            $user->setEmail($email);
            $user->setFirstName('Test');
            $user->setLastName('User');
            
            // Generate a test reset token
            $resetToken = 'test-reset-token-' . uniqid();
            
            // Send password reset email
            $this->emailService->sendPasswordResetEmail($user, $resetToken);
            
            $output->writeln('<info>✅ Password reset email sent successfully!</info>');
            $output->writeln("Check inbox at: {$email}");
            $output->writeln("Reset token: {$resetToken}");
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $output->writeln('<error>❌ Failed to send password reset email:</error>');
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            
            return Command::FAILURE;
        }
    }
}
