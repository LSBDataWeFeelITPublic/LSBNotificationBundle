<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Event;

use LSB\NotificationBundle\Entity\NotificationInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class NotificationEvent
 * @package LSB\NotificationBundle\Event
 */
class NotificationEvent extends Event
{
    protected NotificationInterface $notification;

    /**
     * NotificationEvent constructor.
     * @param NotificationInterface $notification
     */
    public function __construct(NotificationInterface $notification)
    {
        $this->notification = $notification;
    }

    /**
     * @return NotificationInterface
     */
    public function getNotification(): NotificationInterface
    {
        return $this->notification;
    }
}
