<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\Tests;

use LSB\NotificationBundle\Entity\EntityInterface;
use LSB\NotificationBundle\Factory\EntityFactory;
use LSB\NotificationBundle\Factory\EntityFactoryInterface;
use LSB\NotificationBundle\Manager\EntityManager;
use LSB\NotificationBundle\Repository\EntityRepository;
use LSB\NotificationBundle\Repository\EntityRepositoryInterface;
use LSB\UtilityBundle\Manager\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class EntityManagerTest
 * @package LSB\NotificationBundle\Tests
 */
class EntityManagerTest extends TestCase
{
    /**
     * Assert returned interfaces
     * @throws \Exception
     */
    public function testReturnedInterfaces()
    {
        $objectManagerMock = $this->createMock(ObjectManager::class);
        $entityFactoryMock = $this->createMock(EntityFactory::class);
        $entityRepositoryMock = $this->createMock(EntityRepository::class);

        $entityManager = new EntityManager($objectManagerMock, $entityFactoryMock, $entityRepositoryMock, null);

        $this->assertInstanceOf(EntityInterface::class, $entityManager->createNew());
        $this->assertInstanceOf(EntityFactoryInterface::class, $entityManager->getFactory());
        $this->assertInstanceOf(EntityRepositoryInterface::class, $entityManager->getRepository());
    }
}
