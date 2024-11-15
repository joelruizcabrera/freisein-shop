<?php

declare(strict_types=1);

namespace Shopware\Production\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Todo: As the options have now become much more extensive, the logic is becoming increasingly confusing.
 *   A PluginBuilder should therefore be created to make the whole thing more flexible and straightforward.
 */
#[AsCommand(
    name: 'hbh:plugins:create',
    description: 'Creates a project specific hbh-plugin',
)]
class HbHCreatePluginCommand extends Command
{
    public function __construct(private readonly string $projectRootDir)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fs = new Filesystem();

        // Todo:: Implement if needed to create plugins outside "static-plugins"
        //   https://symfony.com/doc/current/components/console/helpers/questionhelper.html#let-the-user-choose-from-a-list-of-answers
        //$pluginFolderQuestion = new ChoiceQuestion()
        $pluginBaseFolder = $this->projectRootDir.'/custom/static-plugins';
        $pluginName = $io->ask('Enter plugin name without HbH-Prefix e.g. ProjectConfig results in HbHProjectConfig', null, function ($answer) {
            if (!is_string($answer)) {
                throw new \RuntimeException(
                    'A plugin name is mandatory.'
                );
            }
            return $answer;
        });

        // Todo: If it starts with hbh remove that part
        if (str_starts_with(strtolower($pluginName), 'hbh')) {
            $pluginName = substr($pluginName, 3);
        }
        $pluginName = 'HbH'.ucfirst($pluginName);
        $pluginRootFolder = $pluginBaseFolder.'/'.$pluginName;
        if ($fs->exists($pluginRootFolder)) {
            $io->error(sprintf('Plugin with name "%s" already exists in "%s"', $pluginName, $pluginBaseFolder));
            return Command::FAILURE;
        }

        $fs->mkdir([
            $pluginRootFolder,
            $pluginRootFolder.'/src',
            $pluginRootFolder.'/src/Resources',
            $pluginRootFolder.'/src/Resources/config',
        ]);

        $useHbhFoundation = $io->confirm('Require HbHFoundation-Plugin?', true);

        $composerPluginName = $this->createComposerJson($fs, $pluginRootFolder, $pluginName, $io, $useHbhFoundation);
        $this->createBootstrap($fs, $pluginRootFolder, $pluginName, $useHbhFoundation);
        $this->copyPluginLogo($fs, $pluginRootFolder);

        if ($io->confirm('Create main.js for administration?', false)) {
            $this->createMainJs($fs, $pluginRootFolder);
        }

        if ($io->confirm('Create storefront snippet files for locales de-DE and en-GB?', false)) {
            $this->createSnippetJson($fs, $pluginRootFolder, $pluginName);
        }

        $defineConfigService = false;
        if ($io->confirm('Create plugin config.xml?', false)) {
            $this->createPluginConfigXml($fs, $pluginRootFolder);
            if ($useHbhFoundation) {
                $this->createPluginConfigPhp($fs, $pluginRootFolder, $pluginName);
                $defineConfigService = true;
            }
        }

        if ($useHbhFoundation && $io->confirm('Create plugin Logging Channel?', true)) {
            $loggerServiceName = $this->createLoggingChannel($fs, $pluginRootFolder, $pluginName);
        }

        if ($io->confirm('Create routes.xml?', false)) {
            $this->createRoutesXml($fs, $pluginRootFolder);
        }

        if ($io->confirm('Create views/storefront folder?', false)) {
            $fs->mkdir($pluginRootFolder.'/src/Resources/views/storefront',);
        }

        $this->createServicesXml($fs, $pluginRootFolder, $defineConfigService, $pluginName);

        $io->success(sprintf('The plugin was successfully created at: %s', $pluginRootFolder));
        $io->caution(sprintf('To activate the plugin execute the following command: composer require %s:*', $composerPluginName));
        if ($defineConfigService) {
            $io->note(sprintf('Use "src/Config/Config.php" to access (plugin)configuration values.'));
        }
        if (isset($loggerServiceName)) {
            $io->note(sprintf('To use the plugins logging channel pass "%s" as service argument for a LoggerInterface requirement.', $loggerServiceName));
        }

        return Command::SUCCESS;
    }

    private function createComposerJson(Filesystem $fs, string $pluginPath, string $pluginName, SymfonyStyle $io, bool $useHbhFoundation): string
    {
        $vendor = substr($pluginName, 0, 3);
        $plugin = substr($pluginName, 3);
        $composerPluginName = implode('-', preg_split('/(?=[A-Z])/', $plugin, -1, PREG_SPLIT_NO_EMPTY));
        $composerName = strtolower($vendor.'/'.$composerPluginName);

        $composerJson = [
            'name' => $composerName,
            'version' => '0.0.1',
            'description' => $io->ask('Composer package description', ''),
            'type' => 'shopware-platform-plugin',
            "authors" => [
                ["name" => "Hob by Horse GmbH"]
            ],
            'autoload' => [
                'psr-4' => [
                    $vendor.'\\'.$plugin.'\\' => 'src/' // "HbH\\SapConnector\\": "src/"
                ]
            ],
            'extra' => [
                'shopware-plugin-class' => $vendor.'\\'.$plugin.'\\'.$pluginName, // "HbH\\SapConnector\\HbHSapConnector",
                'label' => [
                    'de-DE' => $io->ask('Plugin Label [DE]', $pluginName),
                    'en-GB' => $io->ask('Plugin Label [EN]', $pluginName),
                ],
                'description' => [
                    'de-DE' => $io->ask('Plugin Description [DE]', ''),
                    'en-GB' => $io->ask('Plugin Description [EN]', ''),
                ],
            ]
        ];

        if ($useHbhFoundation) {
            $composerJson['require'] = [
                'hbh/foundation' => $io->ask('HbHFoundation version?', '*')
            ];
        }

        $fs->dumpFile($pluginPath . '/composer.json', json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $composerName;
    }

    private function createBootstrap(Filesystem $fs, string $pluginPath, string $pluginName, bool $useHbhFoundation): void
    {
        $vendor = substr($pluginName, 0, 3);
        $plugin = substr($pluginName, 3);

        if ($useHbhFoundation) {
            $tpl = <<<EOL
<?php

declare(strict_types=1);

namespace #namespace#;

use HbH\Foundation\Plugin\HbHFoundationPlugin;

class #class# extends HbHFoundationPlugin
{
}
EOL;
        } else {
            $tpl = <<<EOL
<?php declare(strict_types=1);

namespace #namespace#;

use Shopware\Core\Framework\Plugin;

class #class# extends Plugin
{
}
EOL;
        }

        $fs->dumpFile($pluginPath . '/src/' . $pluginName . '.php', str_replace(['#namespace#', '#class#'], [$vendor.'\\'.$plugin, $pluginName], $tpl));
    }

    private function createServicesXml(Filesystem $fs, string $pluginPath, bool $defineConfigService, string $pluginName): void
    {
        $xml = <<<XML
<?xml version="1.0" ?>

<container xmlns="http://symfony.com/schema/dic/services"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

    <!--Example: For a better overview, services can be split into several files and imported as in the example. -->
    <!--
    <imports>
        <import resource="./services/product.xml"/>
    </imports>
    -->

    <services>
        #services#
    </services>
</container>
XML;

        $services = '';

        if ($defineConfigService) {
            $vendor = substr($pluginName, 0, 3);
            $plugin = substr($pluginName, 3);
            $serviceId =  $vendor.'\\'.$plugin.'\\Config\\Config';
            $configKey = $pluginName.'.config';

            $services = <<<XML
<service id="$serviceId">
            <argument type="service" id="HbH\Foundation\ConfigReader\ConfigReader"/>
            <argument>$configKey</argument>
        </service>
XML;
        }

        $fs->dumpFile($pluginPath.'/src/Resources/config/services.xml', str_replace('#services#', $services, $xml));
    }

    private function copyPluginLogo(Filesystem $fs, string $pluginPath)
    {
        $fs->copy($this->projectRootDir.'/files/hbh-plugin-logo.png', $pluginPath.'/src/Resources/config/plugin.png');
    }

    private function createMainJs(Filesystem $fs, string $pluginPath)
    {
        $fs->dumpFile($pluginPath.'/src/Resources/app/administration/src/main.js', '');
    }

    private function createSnippetJson(Filesystem $fs, string $pluginPath, string $pluginName)
    {
        $vendor = substr($pluginName, 0, 3);
        $plugin = substr($pluginName, 3);
        $snippetRootKey = $vendor.'-'.implode('-', preg_split('/(?=[A-Z])/', $plugin, -1, PREG_SPLIT_NO_EMPTY));

        $locales = ['de-DE', 'en-GB'];
        foreach ($locales as $locale) {
            $snippetJson = [
                strtolower($snippetRootKey) => []
            ];

            $fs->dumpFile($pluginPath . '/src/Resources/snippet/storefront.'.$locale.'.json', json_encode($snippetJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT));
        }
    }

    private function createPluginConfigXml(Filesystem $fs, string $pluginPath)
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>

<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="https://raw.githubusercontent.com/shopware/platform/master/src/Core/System/SystemConfig/Schema/config.xsd">
    <card>

    </card>
</config>
XML;

        $fs->dumpFile($pluginPath.'/src/Resources/config/config.xml', $xml);
    }

    private function createPluginConfigPhp(Filesystem $fs, string $pluginPath, string $pluginName): void
    {
        $vendor = substr($pluginName, 0, 3);
        $plugin = substr($pluginName, 3);

        $tpl = <<<'EOL'
<?php

declare(strict_types=1);

namespace #namespace#;

use HbH\Foundation\ConfigReader\ConfigReaderInterface;

class Config
{
    public function __construct(
        private readonly ConfigReaderInterface $configReader,
        private readonly string $pluginConfigKey
    )
    {
    }

      # Example method to get a plugin config value, replace `myPluginValue` with the key used in the config.xml
//    public function getMyPluginConfigValue(string $salesChannelId = null): string
//    {
//        return rtrim($this->getPluginConfigValue('myPluginValue', true, $salesChannelId), '/');
//    }

    private function getPluginConfigValue(string $configKey, bool $required = true, ?string $salesChannelId = null)
    {
        return $this->configReader->getConfigValue($this->pluginConfigKey.'.'.$configKey, $required, $salesChannelId);
    }
}
EOL;

        $fs->dumpFile($pluginPath . '/src/Config/Config.php', str_replace(['#namespace#'], [$vendor.'\\'.$plugin.'\\Config',], $tpl));
        // Todo: Der Service muss auch noch in der services.xml definiert werden
    }

    private function createRoutesXml(Filesystem $fs, string $pluginPath): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>

<routes xmlns="http://symfony.com/schema/routing"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://symfony.com/schema/routing
        http://symfony.com/schema/routing/routing-1.0.xsd">

    <!--Example: Loads routes from the PHP annotations of the controllers found in that directory-->
    <!--<import resource="../../Controller/" type="annotation" />-->

</routes>
XML;

        $fs->dumpFile($pluginPath.'/src/Resources/config/routes.xml', $xml);
    }

    private function createLoggingChannel(Filesystem $fs, string $pluginPath, string $pluginName): string
    {
        $vendor = substr($pluginName, 0, 3);
        $plugin = substr($pluginName, 3);

        $channelName = strtolower($vendor).'_'.strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $plugin));
        $handlerName = $pluginName.'Handler';
        $logPath = "%kernel.logs_dir%/".$channelName."_%kernel.environment%.log";

        $yaml = <<<EOL
monolog:
  channels: [$channelName]

  handlers:
    $handlerName:
      # The higher the value, the earlier the handler is executed (important for e.g. bubble). Default is 0
      priority: 1
      # Minimum log-level for this handler
      # (debug, info, notice, warning, error, critical, alert, emergency)
      # https://github.com/Seldaek/monolog/blob/main/doc/01-usage.md#log-levels
      level: debug
      # false = Records handled by this handler will not propagate to the following handlers
      bubble: false
      channels: [ $channelName ]
      type: rotating_file
      path: $logPath
      max_files: 10
EOL;
        $fs->dumpFile($pluginPath.'/src/Resources/config/packages/monolog.yaml', $yaml);

        return 'monolog.logger.'.$channelName;
    }
}
