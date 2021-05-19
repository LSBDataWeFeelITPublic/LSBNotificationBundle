<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Entity;

use LSB\UtilityBundle\Interfaces\UuidInterface;

/**
 * Interface NotificationInterface
 * @package LSB\NotificationBundle\Entity
 */
interface NotificationInterface extends UuidInterface
{
    //Statusy wysyłki
    const STATUS_WAITING = 1;
    const STATUS_RETRYING = 20;
    const STATUS_PROCESSING = 30;
    const STATUS_COMPLETED = 100;
    const STATUS_BLOCKED = 110;
    const STATUS_FAILED = 120;
    const STATUS_DATA_ERROR = 200;

    //Strategie przetwarzania
    const STRATEGY_NOW = 1;

    //Typy powiadomień/wysyłek
    const TYPE_SIMPLE = 10;
    const TYPE_EXTENDED = 20;
}