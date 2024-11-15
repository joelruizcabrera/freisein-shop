<?php declare(strict_types=1);

namespace Shopware\Production\Command;

use Shopware\Core\Framework\Context;
use Shopware\Storefront\Theme\ConfigLoader\AbstractAvailableThemeProvider;
use Shopware\Storefront\Theme\ThemeService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'hbh:theme:compile',
    description: 'Compiles the theme of the given/chosen sales channel',
)]
class HbHThemeCompileCommand extends Command
{
    public function __construct(
        private readonly ThemeService $themeService,
        private readonly AbstractAvailableThemeProvider $themeProvider)
    {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->addOption('keep-assets', 'k', InputOption::VALUE_NONE, 'Keep current assets, do not delete them');
        $this->addArgument('salesChannelUuid', InputOption::VALUE_REQUIRED, 'Sales channel id');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $context = Context::createDefaultContext();

        $list = $this->themeProvider->load($context);
        $salesChannelUuid = $input->getArgument('salesChannelUuid');

        if (!$salesChannelUuid) {
            $choices = [];
            foreach ($list as $salesChannelId => $themeId) {
                $choices[] = $salesChannelId;
            }
            $helper = $this->getHelper('question');
            $question = new ChoiceQuestion('Please select which sales channel theme should be compiled.', $choices);
            $question->setErrorMessage('Sales channel %s is invalid.');
            $salesChannelUuid = $helper->ask($input, $output, $question);
        }

        $salesChannelUuid = strtolower($salesChannelUuid);
        $io->block(\sprintf('Compiling theme for sales channel for : %s', $salesChannelUuid));

        $start = microtime(true);
        $this->themeService->compileTheme($salesChannelUuid, $list[$salesChannelUuid], $context, null, !$input->getOption('keep-assets'));
        $io->note(sprintf('Took %f seconds', microtime(true) - $start));

        return self::SUCCESS;
    }
}