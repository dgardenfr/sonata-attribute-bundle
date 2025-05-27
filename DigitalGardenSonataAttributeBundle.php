<?php

namespace DigitalGarden\SonataAttributeBundle;

use Neimheadh\SonataAdminAttributeBundle\DependencyInjection\CompilerPass\AdminCreationCompilerPass;
use Neimheadh\SonataAdminAttributeBundle\DependencyInjection\CompilerPass\AdminFieldAddCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * Sonata Admin helper with attributes.
 */
class DigitalGardenSonataAttributeBundle extends AbstractBundle
{
    /**
     * @param ContainerBuilder $container
     * @return void
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new AdminCreationCompilerPass(), priority: 100);
        $container->addCompilerPass(new AdminFieldAddCompilerPass(), priority: -100);
    }
}