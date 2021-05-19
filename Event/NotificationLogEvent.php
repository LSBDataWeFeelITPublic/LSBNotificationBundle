<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Event;

use LSB\NotificationBundle\Entity\Notification;
use LSB\NotificationBundle\Entity\NotificationRecipient;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class NotificationLogEvent
 * @package LSB\NotificationBundle\Event
 */
class NotificationLogEvent extends Event
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * Powiadomienie
     *
     * @var Notification
     */
    protected $notification;

    /**
     * Odbiorca
     *
     * @var NotificationRecipient|null
     */
    protected $notificationRecipient;

    /**
     * NotificationLogEvent constructor.
     * @param Request $request
     * @param Notification $notification
     * @param NotificationRecipient|null $notificationRecipient
     */
    public function __construct(
        Request $request,
        Notification $notification,
        ?NotificationRecipient $notificationRecipient = null
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
     * @return Notification
     */
    public function getNotification(): Notification
    {
        return $this->notification;
    }

    /**
     * @return NotificationRecipient|null
     */
    public function getNotificationRecipient(): ?NotificationRecipient
    {
        return $this->notificationRecipient;
    }
}
