<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Manager;

use LSB\NotificationBundle\Entity\NotificationInterface;
use LSB\NotificationBundle\Factory\NotificationFactoryInterface;
use LSB\NotificationBundle\Repository\NotificationRepositoryInterface;
use LSB\UtilityBundle\Factory\FactoryInterface;
use LSB\UtilityBundle\Form\BaseEntityType;
use LSB\UtilityBundle\Manager\ObjectManagerInterface;
use LSB\UtilityBundle\Manager\BaseManager;
use LSB\UtilityBundle\Repository\RepositoryInterface;

/**
* Class NotificationManager
* @package LSB\NotificationBundle\Manager
*/
class NotificationManager extends BaseManager
{

    /**
     * NotificationManager constructor.
     * @param ObjectManagerInterface $objectManager
     * @param NotificationFactoryInterface $factory
     * @param NotificationRepositoryInterface $repository
     * @param BaseEntityType|null $form
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        NotificationFactoryInterface $factory,
        NotificationRepositoryInterface $repository,
        ?BaseEntityType $form
    ) {
        parent::__construct($objectManager, $factory, $repository, $form);
    }

    /**
     * @return NotificationInterface|object
     */
    public function createNew(): NotificationInterface
    {
        return parent::createNew();
    }

    /**
     * @return NotificationFactoryInterface|FactoryInterface
     */
    public function getFactory(): NotificationFactoryInterface
    {
        return parent::getFactory();
    }

    /**
     * @return NotificationRepositoryInterface|RepositoryInterface
     */
    public function getRepository(): NotificationRepositoryInterface
    {
        return parent::getRepository();
    }
}
