<?php

declare(strict_types=1);

namespace Shopware\Production\CompilerPass;

use Psr\Log\NullLogger;
use Shopware\Core\Framework\Migration\MigrationCollectionLoader;
use Shopware\Core\Framework\Migration\MigrationRuntime;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Workaround for this Bug: https://issues.shopware.com/issues/NEXT-34417
 * Remove as soon as the bug is fixed.
 */
class RemoveLoggerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (
            !$container->hasDefinition(MigrationCollectionLoader::class)
            && !$container->hasAlias(MigrationCollectionLoader::class)
        ) {
            return;
        }

        $definition = $container->findDefinition(MigrationCollectionLoader::class);
        $definition->replaceArgument(3, null);

        $definition2 = $container->findDefinition(MigrationRuntime::class);
        $definition2->replaceArgument(1, new Reference(NullLogger::class));
    }
}
