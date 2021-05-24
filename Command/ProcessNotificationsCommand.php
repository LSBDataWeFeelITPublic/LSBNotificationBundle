<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Command;

use LSB\NotificationBundle\Manager\NotificationManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ProcessNotificationsCommand
 * @package LSB\NotificationBundle\Command
 */
class ProcessNotificationsCommand extends Command
{
    protected static $defaultName = 'lsb_notification:notification:process';

    protected NotificationManager $notificationManager;

    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Process notifications')
            ->setHelp('');
    }

    /**
     * GenerateCountriesCommand constructor.
     * @param NotificationManager $notificationManager
     */
    public function __construct(NotificationManager $notificationManager)
    {
        parent::__construct();
        $this->notificationManager = $notificationManager;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->notificationManager->processNotifications($output);
        return Command::SUCCESS;
    }
}
