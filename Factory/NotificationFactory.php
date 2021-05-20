<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Factory;

use LSB\NotificationBundle\Entity\NotificationInterface;
use LSB\UtilityBundle\Factory\BaseFactory;

/**
 * Class NotificationFactory
 * @package LSB\NotificationBundle\Factory
 */
class NotificationFactory extends BaseFactory implements NotificationFactoryInterface
{

    /**
     * @return NotificationInterface
     */
    public function createNew(): NotificationInterface
    {
        return parent::createNew();
    }

}
