<?php declare(strict_types=1);

namespace Shopware\Production\Command;

use Shopware\Core\DevOps\Environment\EnvironmentHelper;
use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Maintenance\System\Service\DatabaseConnectionFactory;
use Shopware\Core\Maintenance\System\Service\SetupDatabaseAdapter;
use Shopware\Core\Maintenance\System\Struct\DatabaseConnectionInformation;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'hbh:system:install',
    description: 'Installs the Shopware 6 system',
)]
class HbHSystemInstallCommand extends Command
{
    public function __construct(
        private readonly string $projectDir,
        private readonly SetupDatabaseAdapter $setupDatabaseAdapter,
        private readonly DatabaseConnectionFactory $databaseConnectionFactory,
        private readonly EntityRepository $salesChannelRepository
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('create-database', null, InputOption::VALUE_NONE, 'Create database if it doesn\'t exist.')
            ->addOption('drop-database', null, InputOption::VALUE_NONE, 'Drop existing database')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force install even if install.lock exists')
            ->addOption('shop-name', null, InputOption::VALUE_REQUIRED, 'The name of your shop')
            ->addOption('shop-email', null, InputOption::VALUE_REQUIRED, 'Shop email address')
            ->addOption('shop-locale', null, InputOption::VALUE_REQUIRED, 'Default language locale of the shop')
            ->addOption('shop-currency', null, InputOption::VALUE_REQUIRED, 'Iso code for the default currency of the shop')
            ->addOption('admin-username', null, InputOption::VALUE_REQUIRED, 'Admin username', 'admin')
            ->addOption('admin-password', null, InputOption::VALUE_REQUIRED, 'Admin password', 'shopware')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output = new ShopwareStyle($input, $output);

        // set default
        $isBlueGreen = EnvironmentHelper::getVariable('BLUE_GREEN_DEPLOYMENT', '0');
        $_ENV['BLUE_GREEN_DEPLOYMENT'] = $_SERVER['BLUE_GREEN_DEPLOYMENT'] = $isBlueGreen;
        putenv('BLUE_GREEN_DEPLOYMENT=' . $isBlueGreen);

        if (!$input->getOption('force') && file_exists($this->projectDir . '/install.lock')) {
            $output->comment('install.lock already exists. Delete it or pass --force to do it anyway.');

            return self::FAILURE;
        }

        $this->initializeDatabase($output, $input);

        $commands = [
            [
                'command' => 'system:generate-jwt',
                'allowedToFail' => true,
            ],
            [
                'command' => 'database:migrate',
                'identifier' => 'core',
                '--all' => true,
            ],
            [
                'command' => 'database:migrate-destructive',
                'identifier' => 'core',
                '--all' => true,
                '--version-selection-mode' => 'all',
            ],
            [
                'command' => 'system:configure-shop',
                '--shop-name' => $input->getOption('shop-name'),
                '--shop-email' => $input->getOption('shop-email'),
                '--shop-locale' => $input->getOption('shop-locale'),
                '--shop-currency' => $input->getOption('shop-currency'),
                '--no-interaction' => true,
            ],
            [
                'command' => 'dal:refresh:index',
            ],
            [
                'command' => 'scheduled-task:register',
            ],
            [
                'command' => 'plugin:refresh',
            ],
        ];

        /** @var Application $application */
        $application = $this->getApplication();
        if ($application->has('theme:refresh')) {
            $commands[] = [
                'command' => 'theme:refresh',
            ];
        }

        if ($application->has('theme:compile')) {
            $commands[] = [
                'command' => 'theme:compile',
                'allowedToFail' => true,
            ];
        }

        $commands[] = [
            'command' => 'user:create',
            'username' => $input->getOption('admin-username'),
            '--admin' => true,
            '--password' => $input->getOption('admin-password'),
        ];

        $commands[] = [
            'command' => 'assets:install',
        ];
        $commands[] = [
            'command' => 'cache:clear',
        ];

        $this->runCommands($commands, $output);

        if (!file_exists($this->projectDir . '/public/.htaccess')
            && file_exists($this->projectDir . '/public/.htaccess.dist')
        ) {
            copy($this->projectDir . '/public/.htaccess.dist', $this->projectDir . '/public/.htaccess');
        }

        touch($this->projectDir . '/install.lock');

        // Remove Headless Storefront
        // shopware/core/Migration/V6_3/Migration1536233560BasicData.php::createSalesChannel()
        $this->salesChannelRepository->delete([['id' => '98432def39fc4624b33213a56b8c944d']], Context::createDefaultContext());

        // https://github.com/ByteInternet/HypernodeShopware6Helpers/blob/master/src/Command/SkipWizard.php

        return self::SUCCESS;
    }

    /**
     * @param array<int, array<string, string|bool|null>> $commands
     */
    private function runCommands(array $commands, OutputInterface $output): int
    {
        $application = $this->getApplication();
        if ($application === null) {
            throw new \RuntimeException('No application initialised');
        }

        foreach ($commands as $parameters) {
            // remove params with null value
            $parameters = array_filter($parameters);

            $output->writeln('');

            $output->writeln('Command: '.$parameters['command']);
            $command = $application->find((string) $parameters['command']);
            $allowedToFail = $parameters['allowedToFail'] ?? false;
            unset($parameters['command'], $parameters['allowedToFail']);

            try {
                $returnCode = $command->run(new ArrayInput($parameters, $command->getDefinition()), $output);
                if ($returnCode !== 0 && !$allowedToFail) {
                    return $returnCode;
                }
            } catch (\Throwable $e) {
                if (!$allowedToFail) {
                    throw $e;
                }
            }
        }

        return self::SUCCESS;
    }

    private function initializeDatabase(ShopwareStyle $output, InputInterface $input): void
    {
        $databaseConnectionInformation = DatabaseConnectionInformation::fromEnv();

        $connection = $this->databaseConnectionFactory->getConnection($databaseConnectionInformation, true);

        $output->writeln('Prepare installation');
        $output->writeln('');

        $dropDatabase = $input->getOption('drop-database');
        if ($dropDatabase) {
            $this->setupDatabaseAdapter->dropDatabase($connection, $databaseConnectionInformation->getDatabaseName());
            $output->writeln('Drop database `' . $databaseConnectionInformation->getDatabaseName() . '`');
        }

        $createDatabase = $input->getOption('create-database') || $dropDatabase;
        if ($createDatabase) {
            $this->setupDatabaseAdapter->createDatabase($connection, $databaseConnectionInformation->getDatabaseName());
            $output->writeln('Created database `' . $databaseConnectionInformation->getDatabaseName() . '`');
        }

        $importedBaseSchema = $this->setupDatabaseAdapter->initializeShopwareDb($connection, $databaseConnectionInformation->getDatabaseName());

        if ($importedBaseSchema) {
            $output->writeln('Imported base schema.sql');
        }

        $output->writeln('');
    }
}
