<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Factory;

use LSB\NotificationBundle\Entity\NotificationAttachmentInterface;
use LSB\UtilityBundle\Factory\BaseFactory;

/**
 * Class NotificationAttachmentFactory
 * @package LSB\NotificationBundle\Factory
 */
class NotificationAttachmentFactory extends BaseFactory implements NotificationAttachmentFactoryInterface
{

    /**
     * @return NotificationAttachmentInterface
     */
    public function createNew(): NotificationAttachmentInterface
    {
        return parent::createNew();
    }

}
