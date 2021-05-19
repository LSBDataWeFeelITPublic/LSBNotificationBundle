<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Factory;

use LSB\NotificationBundle\Entity\NotificationBlackListInterface;
use LSB\UtilityBundle\Factory\BaseFactory;

/**
 * Class NotificationBlackListFactory
 * @package LSB\NotificationBundle\Factory
 */
class NotificationBlackListFactory extends BaseFactory implements NotificationBlackListFactoryInterface
{

    /**
     * @return NotificationBlackListInterface
     */
    public function createNew(): NotificationBlackListInterface
    {
        return parent::createNew();
    }

}
