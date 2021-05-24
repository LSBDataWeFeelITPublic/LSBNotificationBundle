<?php
declare(strict_types=1);

namespace LSB\NotificationBundle\DependencyInjection\Compiler;

use LSB\NotificationBundle\Manager\ChannelModuleInventory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class AddChannelModulePass
 * @package LSB\NotificationBundle\DependencyInjection\Compiler
 */
class AddChannelModulePass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(ChannelModuleInventory::class)) {
            return;
        }

        $def = $container->findDefinition(ChannelModuleInventory::class);

        foreach ($container->findTaggedServiceIds('notification.channel') as $id => $attrs) {
            $def->addMethodCall('addModule', [new Reference($id)]);
        }
    }
}
