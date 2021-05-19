<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Factory;

use LSB\NotificationBundle\Entity\NotificationRecipientInterface;
use LSB\UtilityBundle\Factory\BaseFactory;

/**
 * Class NotificationRecipientFactory
 * @package LSB\NotificationBundle\Factory
 */
class NotificationRecipientFactory extends BaseFactory implements NotificationRecipientFactoryInterface
{

    /**
     * @return NotificationRecipientInterface
     */
    public function createNew(): NotificationRecipientInterface
    {
        return parent::createNew();
    }

}
