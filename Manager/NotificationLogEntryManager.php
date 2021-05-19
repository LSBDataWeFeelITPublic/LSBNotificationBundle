<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Manager;

use LSB\NotificationBundle\Entity\NotificationLogEntryInterface;
use LSB\NotificationBundle\Factory\NotificationLogEntryFactoryInterface;
use LSB\NotificationBundle\Repository\NotificationLogEntryRepositoryInterface;
use LSB\UtilityBundle\Factory\FactoryInterface;
use LSB\UtilityBundle\Form\BaseEntityType;
use LSB\UtilityBundle\Manager\ObjectManagerInterface;
use LSB\UtilityBundle\Manager\BaseManager;
use LSB\UtilityBundle\Repository\RepositoryInterface;

/**
* Class NotificationLogEntryManager
* @package LSB\NotificationBundle\Manager
*/
class NotificationLogEntryManager extends BaseManager
{

    /**
     * NotificationLogEntryManager constructor.
     * @param ObjectManagerInterface $objectManager
     * @param NotificationLogEntryFactoryInterface $factory
     * @param NotificationLogEntryRepositoryInterface $repository
     * @param BaseEntityType|null $form
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        NotificationLogEntryFactoryInterface $factory,
        NotificationLogEntryRepositoryInterface $repository,
        ?BaseEntityType $form
    ) {
        parent::__construct($objectManager, $factory, $repository, $form);
    }

    /**
     * @return NotificationLogEntryInterface|object
     */
    public function createNew(): NotificationLogEntryInterface
    {
        return parent::createNew();
    }

    /**
     * @return NotificationLogEntryFactoryInterface|FactoryInterface
     */
    public function getFactory(): NotificationLogEntryFactoryInterface
    {
        return parent::getFactory();
    }

    /**
     * @return NotificationLogEntryRepositoryInterface|RepositoryInterface
     */
    public function getRepository(): NotificationLogEntryRepositoryInterface
    {
        return parent::getRepository();
    }
}
