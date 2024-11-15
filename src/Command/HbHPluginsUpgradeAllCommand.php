<?php declare(strict_types=1);

namespace Shopware\Production\Command;

use MJS\TopSort\Implementations\FixedArraySort;
use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Plugin\PluginCollection;
use Shopware\Core\Framework\Plugin\PluginEntity;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'hbh:plugins:upgrade-all',
    description: 'Upgrades all plugins to the lastest available version',
)]
class HbHPluginsUpgradeAllCommand extends Command
{
    public function __construct(
        private readonly EntityRepository $pluginRepository
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output = new ShopwareStyle($input, $output);

        foreach ($this->getPlugins() as $plugin) {
            if ($plugin->getUpgradeVersion()) {
                $this->runCommand(['command' => 'plugin:update', 'plugins' => [$plugin->getName()]], $output);
            }
        }

        return Command::SUCCESS;
    }

    private function runCommand(array $commandData, OutputInterface $output = null): int
    {
        $command = $this->getApplication()->find($commandData['command']);
        //unset($commandData['command']);
        return $command->run(new ArrayInput($commandData), $output ?? new NullOutput());
    }

    /**
     * With dependency taken into account
     * @return PluginEntity[]
     */
    private function getPlugins(): array
    {
        $context = Context::createDefaultContext();
        $criteria = new Criteria();
        /** @var PluginCollection $plugins */
        $plugins = $this->pluginRepository->search($criteria, $context)->getEntities();

        $shopwarePLuginAndComposerNames = [];
        foreach ($plugins as $plugin) {
            $shopwarePLuginAndComposerNames[$plugin->getComposerName()] = $plugin;
        }

        $composer = json_decode(file_get_contents(dirname(__DIR__, 2).'/composer.lock'), true);
        /**
         * This doesn't include plugins that have no 'require' section.
         */
        $pluginDependencies = array_column($composer['packages'], 'require', 'name');
        // Remove non SW-Plugins
        $pluginDependencies = array_intersect_key($pluginDependencies, $shopwarePLuginAndComposerNames);

        $sorter = new FixedArraySort();
        foreach ($plugins as $plugin) {
            $dependencyList = [];
            /**
             * Plugins without a require section arn't in the pluginDependencies array.
             */
            $dependencies = $pluginDependencies[$plugin->getComposerName()] ?? null;
            if ($dependencies) {
                $dependencyList = array_intersect_key($dependencies, $shopwarePLuginAndComposerNames);
            }

            $sorter->add($plugin->getComposerName(), array_keys($dependencyList));
        }

        $result = [];
        foreach ($sorter->sort() as $pluginPackageName) {
            $result[] = $shopwarePLuginAndComposerNames[$pluginPackageName];
        }

        return $result;
    }
}