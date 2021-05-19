<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Factory;

use LSB\NotificationBundle\Entity\NotificationLogEntryInterface;
use LSB\UtilityBundle\Factory\BaseFactory;

/**
 * Class NotificationLogEntryFactory
 * @package LSB\NotificationBundle\Factory
 */
class NotificationLogEntryFactory extends BaseFactory implements NotificationLogEntryFactoryInterface
{

    /**
     * @return NotificationLogEntryInterface
     */
    public function createNew(): NotificationLogEntryInterface
    {
        return parent::createNew();
    }

}
