<?php

declare(strict_types=1);

namespace Shopware\Production\Service;

use Doctrine\DBAL\Connection;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\StorageAttributes;
use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;

class OrphanedMediaFilesService
{
    private const MEDIA_DIRECTORY = 'media';

    public function __construct(
        private readonly Connection $connection,
        private readonly FilesystemOperator $filesystemPublic
    ) {
    }

    /**
     * @return FileAttributes[]
     */
    public function getOrphanedFiles(): array
    {
        $shopwareMediaPaths = $this->getShopwareMediaPaths();
        $directoryListing = $this->filesystemPublic->listContents(self::MEDIA_DIRECTORY, true);

        $mediaFiles = $directoryListing->filter(static function(StorageAttributes $item) {
            return $item->isFile();
        });

        $orphanedFiles = [];

        /** @var FileAttributes $mediaFile */
        foreach ($mediaFiles->getIterator() as $mediaFile) {
            if (!in_array($mediaFile->path(), $shopwareMediaPaths, true)) {
                $orphanedFiles[] = $mediaFile;
            }
        }

        return $orphanedFiles;
    }

    /**
     * @param FileAttributes[] $orphanedFiles
     */
    public function deleteOrphanedFiles(array $orphanedFiles, ?ShopwareStyle $io = null): void
    {
        foreach ($orphanedFiles as $key => $orphanedFile) {
            if (!$orphanedFile instanceof FileAttributes) {
                $io?->warning(
                    sprintf(
                        'Element with index "%s" is skipped because its no instance of "%s".',
                        $key,
                        FileAttributes::class
                    )
                );
                continue;
            }

            if (!$this->filesystemPublic->has($orphanedFile->path())) {
                $io?->warning(
                    sprintf(
                        'Element with index "%s" is skipped because it does not exist.',
                        $key,
                    )
                );
                continue;
            }

            $this->filesystemPublic->delete($orphanedFile->path());
            $io?->success(sprintf('File "%s" has been deleted', $orphanedFile->path()));
        }
    }

    private function getShopwareMediaPaths(): array
    {
        return $this->connection->executeQuery('SELECT `path` FROM `media`')->fetchFirstColumn();
    }
}
