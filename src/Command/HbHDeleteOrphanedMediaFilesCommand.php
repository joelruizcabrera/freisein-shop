<?php declare(strict_types=1);

namespace Shopware\Production\Command;

use League\Flysystem\FileAttributes;
use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Production\Service\OrphanedMediaFilesService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'hbh:media:delete-orphaned-files',
    description: 'Delete all files in the media folders that no longer exists in the media table',
)]
class HbHDeleteOrphanedMediaFilesCommand extends Command
{
    public function __construct(private readonly OrphanedMediaFilesService $orphanedMediaFilesService)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->addOption('dry-run', description: 'Show list of files to be deleted');
        $this->addOption('force', description: 'Files are deleted without confirmation');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new ShopwareStyle($input, $output);

        if ($input->getOption('force') && $input->getOption('dry-run')) {
            $io->error('The options --force and --dry-run cannot be used together, pick one or the other.');

            return self::FAILURE;
        }

        $force = $input->getOption('force');
        $orphanedMediaFiles = $this->orphanedMediaFilesService->getOrphanedFiles();

        if (!$force) {
            $io->title('Files that would be deleted');

            $io->table(
                ['Filename', 'Filesize', 'Last modified'],
                array_map(
                    fn(FileAttributes $file) => [
                        $file->path(),
                        $this->convertFilesize($file->fileSize()),
                        date('d.m.Y H:i:s', $file->lastModified())
                    ],
                    $orphanedMediaFiles
                )
            );
        }

        if ($input->getOption('dry-run')) {
            return self::SUCCESS;
        }

        if (!$force) {
            $confirm = $io->confirm('Are you sure that you want to delete these files?', false);

            if (!$confirm) {
                $io->caution('Aborting due to user input.');

                return self::SUCCESS;
            }
        }

        $this->orphanedMediaFilesService->deleteOrphanedFiles($orphanedMediaFiles, $io);

        return self::SUCCESS;
    }

    private function convertFilesize(int $size): string
    {
        $base = log($size) / log(1000);
        $suffix = array("", "KB", "MB", "GB", "TB");
        $f_base = floor($base);

        return round(pow(1000, $base - floor($base)), 1) .' '. $suffix[$f_base];
    }
}
