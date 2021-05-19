<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Entity;

use LSB\UtilityBundle\Interfaces\UuidInterface;

/**
 * Interface NotificationLogEntryInterface
 * @package LSB\NotificationBundle\Entity
 */
interface NotificationLogEntryInterface extends UuidInterface
{
    /** @var int The message was displayed in client window */
    const TYPE_TRACKING_DISPLAY = 10;

    /** @var int The message was opened in the browser*/
    const TYPE_TRACKING_OPEN = 100;

    /** @var int Message links were clicked */
    const TYPE_TRACKING_CLICK = 200; //Kliknięcie w link
}