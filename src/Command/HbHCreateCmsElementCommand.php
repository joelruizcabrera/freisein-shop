<?php

declare(strict_types=1);

namespace Shopware\Production\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'hbh:cms:create-element',
    description: 'Creates a cms-element skeleton inside a project specific hbh-plugin',
)]
class HbHCreateCmsElementCommand extends Command
{
    public function __construct(private readonly string $projectRootDir)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fs = new Filesystem();

        $pluginName = $io->ask('Enter the plugin (e.g. HbHProjectCms) where the block skeleton should be created.', null, function ($answer) {
            if (!is_string($answer)) {
                throw new \RuntimeException(
                    'A plugin name is mandatory.'
                );
            }
            return $answer;
        });

        $pluginRootFolder = $this->projectRootDir.'/custom/static-plugins/'.$pluginName;

        if (!$fs->exists($pluginRootFolder)) {
            $io->error(sprintf('Plugin folder "%s" not found', $pluginRootFolder));
            return Command::FAILURE;
        }

        $name = $io->ask('Element-Name (Lower case and hyphen as separator))');
        $name = str_replace(' _', '-', strtolower(trim($name)));

        $elementRootFolder = $pluginRootFolder.'/src/Resources/app/administration/src/module/sw-cms/elements/'.$name;
        $fs->mkdir([
            $elementRootFolder,
            $elementRootFolder.'/component',
            $elementRootFolder.'/config',
            $elementRootFolder.'/preview',

        ]);

        $config = [
            'pluginRootFolder' => $pluginRootFolder,
            'elementRootFolder' => $elementRootFolder,
            'name' => $name,
            'nameCamelCase' => $this->toCamelCase($name),
            'nameUnderscore' => $this->hyphenToUnderscore($name)
        ];

        $this->createMainIndex($fs, $config);
        $this->createComponentIndex($fs, $config);
        $this->createComponentHtml($fs, $config);
        $this->createComponentScss($fs, $config);
        $this->createPreviewIndex($fs, $config);
        $this->createPreviewHtml($fs, $config);
        $this->createPreviewScss($fs, $config);
        $this->createConfigIndex($fs, $config);
        $this->createConfigHtml($fs, $config);
        $this->includeBlock($fs, $config);
        $this->addSnippets($fs, $config);
        $this->createStorefrontTemplate($fs, $config);

        $io->success(sprintf('The CMS-Block was successfully created at: %s', $elementRootFolder));
        $io->note(sprintf('Make sure to run %s to update the administration', 'bin/hbh-build-administration.sh '.$pluginName));

        return Command::SUCCESS;
    }

    private function createMainIndex(Filesystem $fs, array $config): void
    {
        $content = <<<DATA
import './component';
import './config';
import './preview';

Shopware.Service('cmsService').registerCmsElement({
    name: '#name#',
    label: 'sw-cms.elements.#nameCamelCase#.label',
    component: 'sw-cms-el-#name#',
    configComponent: 'sw-cms-el-config-#name#',
    previewComponent: 'sw-cms-el-preview-#name#',
    defaultConfig: {
    }
});
DATA;

        $fs->dumpFile(
            $config['elementRootFolder'] . '/index.js',
            str_replace(
                ['#name#', '#nameCamelCase#'],
                [$config['name'], $config['nameCamelCase']],
                $content
            )
        );
    }

    private function createComponentIndex(Filesystem $fs, array $config): void
    {
        $content = <<<DATA
import template from './sw-cms-el-#name#.html.twig';
import './sw-cms-el-#name#.scss';

Shopware.Component.register('sw-cms-el-#name#', {
    template,

    mixins: [
        'cms-element'
    ],

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('#name#');
        }
    }
});
DATA;

        $fs->dumpFile(
            $config['elementRootFolder'] . '/component/index.js',
            str_replace(
                ['#name#'],
                [$config['name']],
                $content
            )
        );
    }

    private function createComponentHtml(Filesystem $fs, array $config): void
    {
        $content = <<<DATA
{% block sw_cms_element_#nameUnderscore# %}
   <div>
      <h2>#name#</h2>
   </div>
{% endblock %}
DATA;

        $fs->dumpFile(
            $config['elementRootFolder'] . '/component/sw-cms-el-'.$config['name'].'.html.twig',
            str_replace(
                ['#nameUnderscore#', '#name#'],
                [$config['nameUnderscore'], $config['name']],
                $content
            )
        );
    }

    private function createComponentScss(Filesystem $fs, array $config): void
    {
        $fs->dumpFile(
            $config['elementRootFolder'] . '/component/sw-cms-el-'.$config['name'].'.scss',
            ''
        );
    }

    private function createPreviewIndex(Filesystem $fs, array $config): void
    {
        $content = <<<DATA
import template from './sw-cms-el-preview-#name#.html.twig';
import './sw-cms-el-preview-#name#.scss';

Shopware.Component.register('sw-cms-el-preview-#name#', {
    template
});
DATA;

        $fs->dumpFile(
            $config['elementRootFolder'] . '/preview/index.js',
            str_replace(
                ['#name#'],
                [$config['name']],
                $content
            )
        );
    }

    private function createPreviewHtml(Filesystem $fs, array $config): void
    {
        $content = <<<DATA
{% block sw_cms_element_#nameUnderscore#_preview %}
   <div>
      <h3>#name#</h3>
   </div>
{% endblock %}
DATA;

        $fs->dumpFile(
            $config['elementRootFolder'] . '/preview/sw-cms-el-preview-'.$config['name'].'.html.twig',
            str_replace(
                ['#name#', '#nameUnderscore#'],
                [$config['name'], $config['nameUnderscore']],
                $content
            )
        );
    }

    private function createPreviewScss(Filesystem $fs, array $config): void
    {
        $fs->dumpFile(
            $config['elementRootFolder'] . '/preview/sw-cms-el-preview-'.$config['name'].'.scss',
            ''
        );
    }

    private function createConfigIndex(Filesystem $fs, array $config): void
    {
        $content = <<<DATA
import template from './sw-cms-el-config-#name#.html.twig';

Shopware.Component.register('sw-cms-el-config-#name#', {
    template,

    mixins: [
        'cms-element'
    ],

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('#name#');
        }
    }
});
DATA;

        $fs->dumpFile(
            $config['elementRootFolder'] . '/config/index.js',
            str_replace(
                ['#name#'],
                [$config['name']],
                $content
            )
        );
    }

    private function createConfigHtml(Filesystem $fs, array $config): void
    {
        $content = <<<DATA
{% block sw_cms_element_#nameUnderscore#_config %}
   <div>
      <h3>#name#</h3>
   </div>
{% endblock %}
DATA;

        $fs->dumpFile(
            $config['elementRootFolder'] . '/config/sw-cms-el-config-'.$config['name'].'.html.twig',
            str_replace(
                ['#name#', '#nameUnderscore#'],
                [$config['name'], $config['nameUnderscore']],
                $content
            )
        );
    }

    private function includeBlock(Filesystem $fs, array $config)
    {
        $fs->appendToFile(
            $config['pluginRootFolder'].'/src/Resources/app/administration/src/main.js',
            str_replace(
                ['{name}'],
                [$config['name']],
                "import './module/sw-cms/elements/{name}';".PHP_EOL
            )
        );
    }

    private function addSnippets(Filesystem $fs, array $config)
    {
        $snippetFile = $config['pluginRootFolder'].'/src/Resources/app/administration/src/module/sw-cms/snippet/de-DE.json';
        if ($fs->exists($snippetFile)) {
            $json = json_decode(file_get_contents($snippetFile), true);
        } else {
            $json = [];
        }

        $json['sw-cms']['elements'][$config['nameCamelCase']]['label'] = $config['name'];

        $fs->dumpFile($snippetFile, json_encode($json, JSON_PRETTY_PRINT));
    }

    private function createStorefrontTemplate(Filesystem $fs, array $config)
    {
        $content = <<<DATA
{% block sw_cms_element_#nameUnderscore# %}
{% endblock %}
DATA;

        $fs->dumpFile(
            $config['pluginRootFolder'].'/src/Resources/views/storefront/element/cms-element-'.$config['name'].'.html.twig',
            str_replace(
                ['#nameUnderscore#'],
                [$config['nameUnderscore']],
                $content
            )
        );
    }

    /**
     * Transform the given $input string into camelCase.
     *
     * Example:
     * $input = "lorem_ipsum-dolorEst"
     * return = "loremIpsumDolorEst"
     */
    private function toCamelCase (string $input): string
    {
        return lcfirst($this->toPascalCase($input));
    }

    /**
     * Transform the given $input string into PascalCase.
     *
     * Example:
     * $input = "lorem_ipsum-dolorEst"
     * return = "LoremIpsumDolorEst"
     */
    private function toPascalCase (string $input): string
    {
        $delimiters = ['_', '-', '.'];
        return str_replace($delimiters, '', ucwords($input, implode("", $delimiters)));
    }

    private function hyphenToUnderscore(string $input): string
    {
        return str_replace('-', '_', $input);
    }
}
