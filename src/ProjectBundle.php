<?php

declare(strict_types=1);

namespace Shopware\Production;

use Shopware\Core\Framework\Bundle;
use Shopware\Production\CompilerPass\RemoveLoggerCompilerPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class ProjectBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/DependencyInjection/'));
        $loader->load('commands.xml');
        $loader->load('logging.xml');
        $loader->load('bugfixes.xml');
        $loader->load('basic_auth.xml');
        $loader->load('orphaned_media.xml');

        $container->addCompilerPass(new RemoveLoggerCompilerPass());
    }
}
