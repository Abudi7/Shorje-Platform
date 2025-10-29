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
    description: 'Test email sending functionality'
)]
class TestEmailCommand extends Command
{
    public function __construct(
        private MailerInterface $mailer
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $email = (new Email())
                ->from('shorje@abdulrhman-alshalal.com')
                ->to('shorje@abdulrhman-alshalal.com')
                ->subject('اختبار البريد الإلكتروني - شورجي')
                ->html('
                    <h2>اختبار البريد الإلكتروني</h2>
                    <p>هذا اختبار لإرسال البريد الإلكتروني من منصة شورجي.</p>
                    <p>إذا وصلتك هذه الرسالة، فالإعدادات تعمل بشكل صحيح.</p>
                    <hr>
                    <p><small>تم إرسال هذه الرسالة في: ' . date('Y-m-d H:i:s') . '</small></p>
                ');

            $this->mailer->send($email);
            
            $output->writeln('<info>تم إرسال البريد الإلكتروني بنجاح!</info>');
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $output->writeln('<error>فشل في إرسال البريد الإلكتروني: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}