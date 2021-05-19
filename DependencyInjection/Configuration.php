<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\DependencyInjection;

use LSB\NotificationBundle\LSBNotificationBundle;
use LSB\UtilityBundle\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    const CONFIG_KEY = 'lsb_notification';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder(self::CONFIG_KEY);

        $treeBuilder
            ->getRootNode()
            ->children()
            ->bundleTranslationDomainScalar(LSBNotificationBundle::class)->end()
            ->resourcesNode()
            ->children()
//            ->resourceNode(
//                'resource',
//                Entity::class,
//                EntityInterface::class,
//                EntityFactory::class,
//                EntityRepository::class,
//                EntityManager::class,
//                EntityType::class
//            )
//            ->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
