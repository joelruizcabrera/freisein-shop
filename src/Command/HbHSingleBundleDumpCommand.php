<?php declare(strict_types=1);

namespace Shopware\Production\Command;

use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Core\Framework\Plugin\BundleConfigGeneratorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'hbh:bundle:single-bundle-dump',
    description: 'Creates a json file with the configuration for single Shopware bundles.',
)]
class HbHSingleBundleDumpCommand extends Command
{
    public function __construct(
        private readonly BundleConfigGeneratorInterface $bundleDumper,
        private readonly string $projectDir
    )
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->addArgument('pluginNames', InputArgument::IS_ARRAY, 'Plugin names that should be included')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config = $this->bundleDumper->getConfig();
        $newConfig = [];
        foreach ($input->getArgument('pluginNames') as $pluginName) {
            $newConfig[$pluginName] = $config[$pluginName];
        }

        \file_put_contents(
            $this->projectDir . '/' . 'var/plugins.json',
            \json_encode($newConfig, \JSON_PRETTY_PRINT)
        );

        $style = new ShopwareStyle($input, $output);
        $style->success('Dumped plugin configuration.');

        return self::SUCCESS;
    }
}
