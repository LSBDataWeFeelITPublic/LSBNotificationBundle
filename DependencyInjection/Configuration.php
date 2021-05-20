<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\DependencyInjection;

use LSB\NotificationBundle\Entity\Notification;
use LSB\NotificationBundle\Entity\NotificationAttachment;
use LSB\NotificationBundle\Entity\NotificationAttachmentInterface;
use LSB\NotificationBundle\Entity\NotificationBlackList;
use LSB\NotificationBundle\Entity\NotificationBlackListInterface;
use LSB\NotificationBundle\Entity\NotificationInterface;
use LSB\NotificationBundle\Entity\NotificationLogEntry;
use LSB\NotificationBundle\Entity\NotificationLogEntryInterface;
use LSB\NotificationBundle\Entity\NotificationRecipient;
use LSB\NotificationBundle\Entity\NotificationRecipientInterface;
use LSB\NotificationBundle\Factory\NotificationAttachmentFactory;
use LSB\NotificationBundle\Factory\NotificationBlackListFactory;
use LSB\NotificationBundle\Factory\NotificationFactory;
use LSB\NotificationBundle\Factory\NotificationLogEntryFactory;
use LSB\NotificationBundle\Factory\NotificationRecipientFactory;
use LSB\NotificationBundle\Form\NotificationAttachmentType;
use LSB\NotificationBundle\Form\NotificationBlackListType;
use LSB\NotificationBundle\Form\NotificationLogEntryType;
use LSB\NotificationBundle\Form\NotificationRecipientType;
use LSB\NotificationBundle\Form\NotificationType;
use LSB\NotificationBundle\LSBNotificationBundle;
use LSB\NotificationBundle\Manager\NotificationAttachmentManager;
use LSB\NotificationBundle\Manager\NotificationBlackListManager;
use LSB\NotificationBundle\Manager\NotificationLogEntryManager;
use LSB\NotificationBundle\Manager\NotificationManager;
use LSB\NotificationBundle\Manager\NotificationRecipientManager;
use LSB\NotificationBundle\Repository\NotificationAttachmentRepository;
use LSB\NotificationBundle\Repository\NotificationBlackListRepository;
use LSB\NotificationBundle\Repository\NotificationLogEntryRepository;
use LSB\NotificationBundle\Repository\NotificationRecipientRepository;
use LSB\NotificationBundle\Repository\NotificationRepository;
use LSB\UtilityBundle\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    const CONFIG_KEY = 'lsb_notification';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder(self::CONFIG_KEY);

        $treeBuilder
            ->getRootNode()
            ->children()
            ->bundleTranslationDomainScalar(LSBNotificationBundle::class)->end()
            ->resourcesNode()
            ->children()
            ->resourceNode(
                'notification',
                Notification::class,
                NotificationInterface::class,
                NotificationFactory::class,
                NotificationRepository::class,
                NotificationManager::class,
                NotificationType::class
            )
            ->end()
            ->resourceNode(
                'notification_attachment',
                NotificationAttachment::class,
                NotificationAttachmentInterface::class,
                NotificationAttachmentFactory::class,
                NotificationAttachmentRepository::class,
                NotificationAttachmentManager::class,
                NotificationAttachmentType::class
            )
            ->end()
            ->resourceNode(
                'notification_black_list',
                NotificationBlackList::class,
                NotificationBlackListInterface::class,
                NotificationBlackListFactory::class,
                NotificationBlackListRepository::class,
                NotificationBlackListManager::class,
                NotificationBlackListType::class
            )
            ->end()
            ->resourceNode(
                'notification_log_entry',
                NotificationLogEntry::class,
                NotificationLogEntryInterface::class,
                NotificationLogEntryFactory::class,
                NotificationLogEntryRepository::class,
                NotificationLogEntryManager::class,
                NotificationLogEntryType::class
            )
            ->end()
            ->resourceNode(
                'notification_recipient',
                NotificationRecipient::class,
                NotificationRecipientInterface::class,
                NotificationRecipientFactory::class,
                NotificationRecipientRepository::class,
                NotificationRecipientManager::class,
                NotificationRecipientType::class
            )
            ->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
