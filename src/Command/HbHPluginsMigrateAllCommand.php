<?php declare(strict_types=1);

namespace Shopware\Production\Command;

use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Migration\Exception\UnknownMigrationSourceException;
use Shopware\Core\Framework\Plugin\PluginCollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'hbh:plugins:migrate-all',
    description: 'Installs the Shopware 6 system',
)]
class HbHPluginsMigrateAllCommand extends Command
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

        $context = Context::createDefaultContext();
        $criteria = new Criteria();
        /** @var PluginCollection $plugins */
        $plugins = $this->pluginRepository->search($criteria, $context)->getEntities();

        $command = $this->getApplication()->find('database:migrate');
        foreach ($plugins as $plugin) {
            if ($plugin->getActive()) {
                try {
                    $input = new ArrayInput([
                        'command' => 'database:migrate', // This is needed, otherwise an exception occurs "Not enough arguments (missing: "command")."
                        'identifier' => $plugin->getName(),
                        '--all'  => true
                    ]);
                    $command->run($input, $output);
                } catch (UnknownMigrationSourceException $e) {
                    $output->writeln(sprintf('Skip migration for plugin "%s" no migrations available.', $plugin->getName()));
                    // Todo: Ignore. This happends if a plugin has no migration files yet.
                }
            }
        }

        return Command::SUCCESS;
    }
}
