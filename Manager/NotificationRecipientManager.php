<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Manager;

use LSB\NotificationBundle\Entity\NotificationRecipientInterface;
use LSB\NotificationBundle\Factory\NotificationRecipientFactoryInterface;
use LSB\NotificationBundle\Repository\NotificationRecipientRepositoryInterface;
use LSB\UtilityBundle\Factory\FactoryInterface;
use LSB\UtilityBundle\Form\BaseEntityType;
use LSB\UtilityBundle\Manager\ObjectManagerInterface;
use LSB\UtilityBundle\Manager\BaseManager;
use LSB\UtilityBundle\Repository\RepositoryInterface;

/**
* Class NotificationRecipientManager
* @package LSB\NotificationBundle\Manager
*/
class NotificationRecipientManager extends BaseManager
{

    /**
     * NotificationRecipientManager constructor.
     * @param ObjectManagerInterface $objectManager
     * @param NotificationRecipientFactoryInterface $factory
     * @param NotificationRecipientRepositoryInterface $repository
     * @param BaseEntityType|null $form
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        NotificationRecipientFactoryInterface $factory,
        NotificationRecipientRepositoryInterface $repository,
        ?BaseEntityType $form
    ) {
        parent::__construct($objectManager, $factory, $repository, $form);
    }

    /**
     * @return NotificationRecipientInterface|object
     */
    public function createNew(): NotificationRecipientInterface
    {
        return parent::createNew();
    }

    /**
     * @return NotificationRecipientFactoryInterface|FactoryInterface
     */
    public function getFactory(): NotificationRecipientFactoryInterface
    {
        return parent::getFactory();
    }

    /**
     * @return NotificationRecipientRepositoryInterface|RepositoryInterface
     */
    public function getRepository(): NotificationRecipientRepositoryInterface
    {
        return parent::getRepository();
    }
}
