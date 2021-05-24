<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Event;

use LSB\NotificationBundle\Entity\Notification;
use LSB\NotificationBundle\Entity\NotificationInterface;
use LSB\NotificationBundle\Entity\NotificationRecipient;
use LSB\NotificationBundle\Entity\NotificationRecipientInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class NotificationLogEvent
 * @package LSB\NotificationBundle\Event
 */
class NotificationLogEvent extends Event
{
    /**
     * @var Request
     */
    protected Request $request;

    /**
     * Powiadomienie
     *
     * @var NotificationInterface
     */
    protected NotificationInterface $notification;

    /**
     * Odbiorca
     *
     * @var NotificationRecipient|null
     */
    protected $notificationRecipient;

    /**
     * NotificationLogEvent constructor.
     * @param Request $request
     * @param NotificationInterface $notification
     * @param NotificationRecipientInterface|null $notificationRecipient
     */
    public function __construct(
        Request $request,
        NotificationInterface $notification,
        ?NotificationRecipientInterface $notificationRecipient = null
    ) {
        $this->request = $request;
        $this->notification = $notification;
        $this->notificationRecipient = $notificationRecipient;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return NotificationInterface
     */
    public function getNotification(): NotificationInterface
    {
        return $this->notification;
    }

    /**
     * @return NotificationRecipientInterface|null
     */
    public function getNotificationRecipient(): ?NotificationRecipientInterface
    {
        return $this->notificationRecipient;
    }
}
