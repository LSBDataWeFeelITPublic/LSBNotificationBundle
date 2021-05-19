<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Entity;

use LSB\UtilityBundle\Interfaces\UuidInterface;

/**
 * Interface NotificationLogEntryInterface
 * @package LSB\NotificationBundle\Entity
 */
interface NotificationRecipientInterface extends UuidInterface
{
    //Statusy wysyłki
    const STATUS_WAITING = 1;
    const STATUS_RETRYING = 2;
    const STATUS_PROCESSING = 10;
    const STATUS_COMPLETED = 100;
    const STATUS_BLOCKED = 101;
    const STATUS_FAILED = 102;
}