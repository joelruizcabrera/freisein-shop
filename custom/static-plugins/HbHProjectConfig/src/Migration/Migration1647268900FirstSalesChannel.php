<?php

declare(strict_types=1);

namespace HbH\ProjectConfig\Migration;

use Doctrine\DBAL\Connection;
use HbH\ProjectConfig\Defaults;
use HbH\ProjectConfig\Migration\DTO\SalesChannelDTO;
use HbH\ProjectConfig\Migration\Traits\CreateSalesChannelTrait;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1647268900FirstSalesChannel extends MigrationStep
{
    use CreateSalesChannelTrait;

    public function getCreationTimestamp(): int
    {
        return 1647268900;
    }

    public function update(Connection $connection): void
    {
        $this->createSalesChannel(
            $connection,
            new SalesChannelDTO(
                Defaults::FIRST_SALES_CHANNEL,
                'Erster Verkaufskanal',
                SalesChannelDTO::TYPE_STOREFRONT,
                $connection
            )
        );
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
