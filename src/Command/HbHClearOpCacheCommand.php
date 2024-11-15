<?php declare(strict_types=1);

namespace Shopware\Production\Command;

use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'hbh:clear-opcache',
    description: 'Clears OPcache',
)]
class HbHClearOpCacheCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new ShopwareStyle($input, $output);

        $io->comment('Start cleaning OPcache');
        opcache_reset();
        $io->success('OPcache cleared');
        return self::SUCCESS;
    }
}
