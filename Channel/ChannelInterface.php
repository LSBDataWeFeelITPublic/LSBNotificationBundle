<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Channel;

use LSB\NotificationBundle\Entity\Notification;
use LSB\NotificationBundle\Entity\NotificationInterface;
use LSB\NotificationBundle\Entity\NotificationRecipient;
use LSB\NotificationBundle\Entity\NotificationRecipientInterface;
use LSB\UtilityBundle\Module\ModuleInterface;

/**
 * Interface ChannelInterface
 * @package LSB\NotificationBundle\Channel
 */
interface ChannelInterface extends ModuleInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param NotificationInterface $notification
     * @param array $recipientsToProcess
     * @return array
     */
    public function sendSimpleNotification(NotificationInterface $notification, array $recipientsToProcess): array;

    /**
     * @param NotificationInterface $notification
     * @param array $recipientsPackage
     * @return array
     */
    public function sendExtendedNotification(NotificationInterface $notification, array $recipientsPackage): array;

    /**
     * @param array $recipients
     * @return array
     */
    public function validateSimpleRecipients(array $recipients): array;

    /**
     * @param $recipient
     * @return mixed
     */
    public function validateSimpleRecipient($recipient);

    /**
     * @param NotificationRecipientInterface $recipient
     * @return mixed
     */
    public function validateExtendedRecipient(NotificationRecipientInterface $recipient);

    /**
     * Converts data to the data form specified by the channels (depending on the object)
     *
     * @param $template
     * @param array $templateData
     * @return mixed
     */
    public function convertDataIntoContent($template, array $templateData);

    /**
     * Status
     *
     * @return mixed
     */
    public function getStatus();

    /**
     * Returns the maximum number of recipients for one shipping cycle
     *
     * @return int
     */
    public function getMaxRecipients(): int;

    /**
     * Dedicated parsing method that takes into account the specificity of the channel - it bases its operation on a public notifications parser
     *
     * @param Notification $notification
     * @param NotificationRecipient $notificationRecipient
     * @return Notification
     */
    public function parseContent(NotificationInterface $notification, NotificationRecipientInterface $notificationRecipient): NotificationInterface;

}
