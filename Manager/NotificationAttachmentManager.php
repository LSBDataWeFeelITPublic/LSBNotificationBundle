<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Manager;

use LSB\NotificationBundle\Entity\NotificationAttachmentInterface;
use LSB\NotificationBundle\Factory\NotificationAttachmentFactoryInterface;
use LSB\NotificationBundle\Repository\NotificationAttachmentRepositoryInterface;
use LSB\UtilityBundle\Factory\FactoryInterface;
use LSB\UtilityBundle\Form\BaseEntityType;
use LSB\UtilityBundle\Manager\ObjectManagerInterface;
use LSB\UtilityBundle\Manager\BaseManager;
use LSB\UtilityBundle\Repository\RepositoryInterface;

/**
* Class NotificationAttachmentManager
* @package LSB\NotificationBundle\Manager
*/
class NotificationAttachmentManager extends BaseManager
{

    /**
     * NotificationAttachmentManager constructor.
     * @param ObjectManagerInterface $objectManager
     * @param NotificationAttachmentFactoryInterface $factory
     * @param NotificationAttachmentRepositoryInterface $repository
     * @param BaseEntityType|null $form
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        NotificationAttachmentFactoryInterface $factory,
        NotificationAttachmentRepositoryInterface $repository,
        ?BaseEntityType $form
    ) {
        parent::__construct($objectManager, $factory, $repository, $form);
    }

    /**
     * @return NotificationAttachmentInterface|object
     */
    public function createNew(): NotificationAttachmentInterface
    {
        return parent::createNew();
    }

    /**
     * @return NotificationAttachmentFactoryInterface|FactoryInterface
     */
    public function getFactory(): NotificationAttachmentFactoryInterface
    {
        return parent::getFactory();
    }

    /**
     * @return NotificationAttachmentRepositoryInterface|RepositoryInterface
     */
    public function getRepository(): NotificationAttachmentRepositoryInterface
    {
        return parent::getRepository();
    }
}
