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
    name: 'hbh:cms:create-block',
    description: 'Creates a cms-block skeleton inside a project specific hbh-plugin',
)]
class HbHCreateCmsBlockCommand extends Command
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

        $helper = $this->getHelper('question');
        $categories = ['commerce', 'form', 'image', 'sidebar', 'text-image', 'text', 'video'];
        $question = new ChoiceQuestion(
            'Please select a category',
            $categories,
        );
        $question->setAutocompleterValues($categories);
        $question->setErrorMessage('Category %s is invalid.');

        $category = $helper->ask($input, $output, $question);
        $name = $io->ask('Block-Name (Lower case and hyphen as separator))');
        $name = str_replace(' _', '-', strtolower(trim($name)));

        $slots = [];
        $i=0;
        do {
            $slots[$i]['name'] = $io->ask('Slot-Name');
            $slots[$i]['component'] = $io->ask('Slot-Component');
            $i++;
        } while ($io->confirm('Add another slot?', false));

        $blockRootFolder = $pluginRootFolder.'/src/Resources/app/administration/src/module/sw-cms/blocks/'.$category.'/'.$name;
        $fs->mkdir([
            $blockRootFolder,
            $blockRootFolder.'/component',
            $blockRootFolder.'/preview',

        ]);

        $config = [
            'pluginRootFolder' => $pluginRootFolder,
            'blockRootFolder' => $blockRootFolder,
            'name' => $name,
            'nameCamelCase' => $this->toCamelCase($name),
            'nameUnderscore' => $this->hyphenToUnderscore($name),
            'category' => $category,
            'categoryCamelCase' => $this->toCamelCase($category),
            'slots' => $slots
        ];

        $this->createMainIndex($fs, $config);
        $this->createComponentIndex($fs, $config);
        $this->createComponentHtml($fs, $config);
        $this->createComponentScss($fs, $config);
        $this->createPreviewIndex($fs, $config);
        $this->createPreviewHtml($fs, $config);
        $this->createPreviewScss($fs, $config);
        $this->includeBlock($fs, $config);
        $this->addSnippets($fs, $config);
        $this->createStorefrontTemplate($fs, $config);

        $io->success(sprintf('The CMS-Block was successfully created at: %s', $blockRootFolder));
        $io->note(sprintf('Make sure to run %s to update the administration', 'bin/hbh-build-administration.sh '.$pluginName));

        return Command::SUCCESS;
    }

    private function createMainIndex(Filesystem $fs, array $config): void
    {
        $content = <<<DATA
import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: '#name#',
    category: '#category#',
    label: 'sw-cms.blocks.#categoryCamelCase#.#nameCamelCase#.label',
    component: 'sw-cms-block-#name#',
    previewComponent: 'sw-cms-preview-#name#',
    defaultConfig: {
        marginBottom: '0',
        marginTop: '0',
        marginLeft: '0',
        marginRight: '0',
        sizingMode: 'boxed'
    },
    slots: {
        #slots#
    }
});
DATA;

        $slotArray = [];
        foreach ($config['slots'] as $slot) {
            $slotArray[] = $slot['name'].": '".$slot['component']."'";
        }
        $slots = implode(",".PHP_EOL, $slotArray);

        $fs->dumpFile(
            $config['blockRootFolder'] . '/index.js',
            str_replace(
                ['#name#', '#category#', '#categoryCamelCase#', '#nameCamelCase#', '#slots#'],
                [$config['name'], $config['category'], $config['categoryCamelCase'], $config['nameCamelCase'], $slots],
                $content
            )
        );
    }

    private function createComponentIndex(Filesystem $fs, array $config): void
    {
        $content = <<<DATA
import template from './sw-cms-block-#name#.html.twig';
import './sw-cms-block-#name#.scss';

Shopware.Component.register('sw-cms-block-#name#', {
    template
});
DATA;

        $fs->dumpFile(
            $config['blockRootFolder'] . '/component/index.js',
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
{% block sw_cms_block_#nameUnderscore# %}
   <div>
      #slots#
   </div>
{% endblock %}
DATA;

        $slots = '';
        foreach ($config['slots'] as $slot) {
            $slots .= '<slot name="'.$slot['name'].'">{% block sw_cms_block_'.$config['nameUnderscore'].'_slot_'.$slot['name'].' %}{% endblock %}</slot>'."\r\n";
        }

        $fs->dumpFile(
            $config['blockRootFolder'] . '/component/sw-cms-block-'.$config['name'].'.html.twig',
            str_replace(
                ['#nameUnderscore#', '#slots#'],
                [$config['nameUnderscore'], $slots],
                $content
            )
        );
    }

    private function createComponentScss(Filesystem $fs, array $config): void
    {
        $fs->dumpFile(
            $config['blockRootFolder'] . '/component/sw-cms-block-'.$config['name'].'.scss',
            ''
        );
    }

    private function createPreviewIndex(Filesystem $fs, array $config): void
    {
        $content = <<<DATA
import template from './sw-cms-preview-#name#.html.twig';
import './sw-cms-preview-#name#.scss';

Shopware.Component.register('sw-cms-preview-#name#', {
    template
});
DATA;

        $fs->dumpFile(
            $config['blockRootFolder'] . '/preview/index.js',
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
{% block sw_cms_preview_#nameUnderscore# %}
   <div>
      <h3>#name#</h3>
   </div>
{% endblock %}
DATA;

        $fs->dumpFile(
            $config['blockRootFolder'] . '/preview/sw-cms-preview-'.$config['name'].'.html.twig',
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
            $config['blockRootFolder'] . '/preview/sw-cms-preview-'.$config['name'].'.scss',
            ''
        );
    }

    private function includeBlock(Filesystem $fs, array $config)
    {
        $fs->appendToFile(
            $config['pluginRootFolder'].'/src/Resources/app/administration/src/main.js',
            str_replace(
                ['{category}', '{name}'],
                [$config['category'], $config['name']],
                "import './module/sw-cms/blocks/{category}/{name}';".PHP_EOL
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

        $json['sw-cms']['blocks'][$config['categoryCamelCase']][$config['nameCamelCase']]['label'] = $config['name'];

        $fs->dumpFile($snippetFile, json_encode($json, JSON_PRETTY_PRINT));
    }

    private function createStorefrontTemplate(Filesystem $fs, array $config)
    {
        $content = <<<DATA
{% block sw_cms_block_#nameUnderscore# %}
{% endblock %}
DATA;

        $fs->dumpFile(
            $config['pluginRootFolder'].'/src/Resources/views/storefront/block/cms-block-'.$config['name'].'.html.twig',
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
