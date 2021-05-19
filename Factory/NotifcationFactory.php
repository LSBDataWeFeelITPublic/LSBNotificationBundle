<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Factory;

use LSB\NotificationBundle\Entity\NotifcationInterface;
use LSB\UtilityBundle\Factory\BaseFactory;

/**
 * Class NotifcationFactory
 * @package LSB\NotificationBundle\Factory
 */
class NotifcationFactory extends BaseFactory implements NotifcationFactoryInterface
{

    /**
     * @return NotifcationInterface
     */
    public function createNew(): NotifcationInterface
    {
        return parent::createNew();
    }

}
