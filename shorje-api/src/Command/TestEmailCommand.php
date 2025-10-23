<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'app:test-email',
    description: 'Test SMTP email sending',
)]
class TestEmailCommand extends Command
{
    public function __construct(
        private MailerInterface $mailer,
        private string $fromEmail,
        private string $fromName
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Testing SMTP email configuration...');
        
        try {
            $email = (new Email())
                ->from($this->fromEmail)
                ->to($this->fromEmail) // Send to same email for testing
                ->subject('Test Email from Shorje Platform')
                ->html('
                    <h2>Test Email</h2>
                    <p>This is a test email to verify SMTP configuration.</p>
                    <p>If you receive this email, the SMTP setup is working correctly!</p>
                    <p>Time: ' . date('Y-m-d H:i:s') . '</p>
                ');

            $this->mailer->send($email);
            
            $output->writeln('<info>✅ Email sent successfully!</info>');
            $output->writeln('Check your inbox at: ' . $this->fromEmail);
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $output->writeln('<error>❌ Failed to send email:</error>');
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            $output->writeln('');
            $output->writeln('Common issues:');
            $output->writeln('1. Gmail App Password not set correctly');
            $output->writeln('2. 2FA not enabled on Gmail account');
            $output->writeln('3. "Less secure app access" not enabled');
            $output->writeln('4. Firewall blocking SMTP port 587');
            
            return Command::FAILURE;
        }
    }
}
