<?php

namespace LSB\NotificationBundle\Event;

use LSB\NotificationBundle\Entity\Notification;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class NotificationEvent
 * @package LSB\NotificationBundle\Event
 */
class NotificationEvent extends Event
{
    protected $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    public function getNotification()
    {
        return $this->notification;
    }
}
