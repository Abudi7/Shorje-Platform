<?php

namespace App\Command;

use App\Service\EmailService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:test-contact-form',
    description: 'Test contact form email sending',
)]
class TestContactFormCommand extends Command
{
    public function __construct(
        private EmailService $emailService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Testing contact form email...');
        
        try {
            $this->emailService->sendContactFormEmail(
                'Test User',
                'test@example.com',
                'Test Contact Form',
                'This is a test message from the contact form to verify email sending functionality.'
            );
            
            $output->writeln('<info>✅ Contact form email sent successfully!</info>');
            $output->writeln('Check admin inbox for the contact form email.');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $output->writeln('<error>❌ Failed to send contact form email:</error>');
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            
            return Command::FAILURE;
        }
    }
}
