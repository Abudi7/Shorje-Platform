<?php

namespace App\Command;

use App\Service\NotificationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:send-platform-notification',
    description: 'Send a platform-wide notification to all users',
)]
class SendPlatformNotificationCommand extends Command
{
    public function __construct(
        private NotificationService $notificationService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('title', InputArgument::REQUIRED, 'Notification title')
            ->addArgument('message', InputArgument::REQUIRED, 'Notification message')
            ->addArgument('actionUrl', InputArgument::OPTIONAL, 'Action URL (optional)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $title = $input->getArgument('title');
        $message = $input->getArgument('message');
        $actionUrl = $input->getArgument('actionUrl');

        $io->info('Sending platform notification to all users...');
        $io->title($title);
        $io->text($message);
        
        try {
            $notifications = $this->notificationService->createPlatformUpdateNotification(
                $title,
                $message,
                $actionUrl
            );
            
            $count = count($notifications);
            $io->success("✅ Successfully sent notification to {$count} users!");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to send notifications: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

