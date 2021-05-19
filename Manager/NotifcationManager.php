<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Manager;

use LSB\NotificationBundle\Entity\NotifcationInterface;
use LSB\NotificationBundle\Factory\NotifcationFactoryInterface;
use LSB\NotificationBundle\Repository\NotifcationRepositoryInterface;
use LSB\UtilityBundle\Factory\FactoryInterface;
use LSB\UtilityBundle\Form\BaseEntityType;
use LSB\UtilityBundle\Manager\ObjectManagerInterface;
use LSB\UtilityBundle\Manager\BaseManager;
use LSB\UtilityBundle\Repository\RepositoryInterface;

/**
* Class NotifcationManager
* @package LSB\NotificationBundle\Manager
*/
class NotifcationManager extends BaseManager
{

    /**
     * NotifcationManager constructor.
     * @param ObjectManagerInterface $objectManager
     * @param NotifcationFactoryInterface $factory
     * @param NotifcationRepositoryInterface $repository
     * @param BaseEntityType|null $form
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        NotifcationFactoryInterface $factory,
        NotifcationRepositoryInterface $repository,
        ?BaseEntityType $form
    ) {
        parent::__construct($objectManager, $factory, $repository, $form);
    }

    /**
     * @return NotifcationInterface|object
     */
    public function createNew(): NotifcationInterface
    {
        return parent::createNew();
    }

    /**
     * @return NotifcationFactoryInterface|FactoryInterface
     */
    public function getFactory(): NotifcationFactoryInterface
    {
        return parent::getFactory();
    }

    /**
     * @return NotifcationRepositoryInterface|RepositoryInterface
     */
    public function getRepository(): NotifcationRepositoryInterface
    {
        return parent::getRepository();
    }
}
