<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Manager;

use LSB\NotificationBundle\Entity\NotificationBlackListInterface;
use LSB\NotificationBundle\Factory\NotificationBlackListFactoryInterface;
use LSB\NotificationBundle\Repository\NotificationBlackListRepositoryInterface;
use LSB\UtilityBundle\Factory\FactoryInterface;
use LSB\UtilityBundle\Form\BaseEntityType;
use LSB\UtilityBundle\Manager\ObjectManagerInterface;
use LSB\UtilityBundle\Manager\BaseManager;
use LSB\UtilityBundle\Repository\RepositoryInterface;

/**
* Class NotificationBlackListManager
* @package LSB\NotificationBundle\Manager
*/
class NotificationBlackListManager extends BaseManager
{

    /**
     * NotificationBlackListManager constructor.
     * @param ObjectManagerInterface $objectManager
     * @param NotificationBlackListFactoryInterface $factory
     * @param NotificationBlackListRepositoryInterface $repository
     * @param BaseEntityType|null $form
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        NotificationBlackListFactoryInterface $factory,
        NotificationBlackListRepositoryInterface $repository,
        ?BaseEntityType $form
    ) {
        parent::__construct($objectManager, $factory, $repository, $form);
    }

    /**
     * @return NotificationBlackListInterface|object
     */
    public function createNew(): NotificationBlackListInterface
    {
        return parent::createNew();
    }

    /**
     * @return NotificationBlackListFactoryInterface|FactoryInterface
     */
    public function getFactory(): NotificationBlackListFactoryInterface
    {
        return parent::getFactory();
    }

    /**
     * @return NotificationBlackListRepositoryInterface|RepositoryInterface
     * @throws \Exception
     */
    public function getRepository(): NotificationBlackListRepositoryInterface
    {
        return parent::getRepository();
    }
}
