<?php
declare(strict_types=1);

namespace LSB\NotificationBundle;

use LSB\NotificationBundle\DependencyInjection\Compiler\AddChannelModulePass;
use LSB\NotificationBundle\DependencyInjection\Compiler\AddManagerResourcePass;
use LSB\NotificationBundle\DependencyInjection\Compiler\AddResolveEntitiesPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class LSBTemplateVendorSF5Bundle
 * @package LSB\NotificationBundle
 */
class LSBNotificationBundle extends Bundle
{
    public function build(ContainerBuilder $builder)
    {
        parent::build($builder);

        $builder
            ->addCompilerPass(new AddChannelModulePass())
            ->addCompilerPass(new AddManagerResourcePass())
            ->addCompilerPass(new AddResolveEntitiesPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1);
        ;
    }
}
