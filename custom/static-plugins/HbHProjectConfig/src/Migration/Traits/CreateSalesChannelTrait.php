<?php

declare(strict_types=1);

namespace HbH\ProjectConfig\Migration\Traits;

use Doctrine\DBAL\Connection;
use HbH\ProjectConfig\Migration\DTO\SalesChannelDTOInterface;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Api\Util\AccessKeyHelper;
use Shopware\Core\Framework\Uuid\Uuid;

trait CreateSalesChannelTrait
{
    public function createSalesChannel(Connection $connection, SalesChannelDTOInterface $salesChannelDTO): void
    {
        $id = Uuid::fromHexToBytes($salesChannelDTO->getId());
        $defaultCountryId = Uuid::fromHexToBytes($salesChannelDTO->getDefaultCountryId());
        $defaultCurrencyId = Uuid::fromHexToBytes($salesChannelDTO->getDefaultCurrencyId());
        $defaultLanguageId = Uuid::fromHexToBytes($salesChannelDTO->getDefaultLanguageId());
        $defaultPaymentMethodId = Uuid::fromHexToBytes($salesChannelDTO->getDefaultPaymentMethodId());
        $defaultShippingMethodId = Uuid::fromHexToBytes($salesChannelDTO->getDefaultShippingMethodId());

        $connection->insert('sales_channel', [
            'id' => $id,
            'type_id' => Uuid::fromHexToBytes($salesChannelDTO->getTypeId()),
            'access_key' => AccessKeyHelper::generateAccessKey('sales-channel'),
            'language_id' => $defaultLanguageId,
            'currency_id' => $defaultCurrencyId,
            'payment_method_id' => $defaultPaymentMethodId,
            'shipping_method_id' => $defaultShippingMethodId,
            'country_id' => $defaultCountryId,
            'navigation_category_id' => Uuid::fromHexToBytes($salesChannelDTO->getNavigationCategoryId()),
            'navigation_category_version_id' => Uuid::fromHexToBytes($salesChannelDTO->getNavigationCategoryVersionId()),
            'active' => $salesChannelDTO->isActive(),
            'customer_group_id' => Uuid::fromHexToBytes($salesChannelDTO->getDefaultCustomerGroupId()),
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        // country
        $connection->insert('sales_channel_country', [
            'sales_channel_id' => $id,
            'country_id' => $defaultCountryId,
        ]);

        // currency
        $connection->insert('sales_channel_currency', [
            'sales_channel_id' => $id,
            'currency_id' => $defaultCurrencyId,
        ]);

        // Language
        $connection->insert('sales_channel_language', [
            'sales_channel_id' => $id,
            'language_id' => $defaultLanguageId,
        ]);

        // Payment method
        $connection->insert('sales_channel_payment_method', [
            'sales_channel_id' => $id,
            'payment_method_id' => $defaultPaymentMethodId,
        ]);

        // Shipping method
        $connection->insert('sales_channel_shipping_method', [
            'sales_channel_id' => $id,
            'shipping_method_id' => $defaultShippingMethodId,
        ]);

        // Translation
        $connection->insert('sales_channel_translation', [
            'sales_channel_id' => $id,
            'language_id' => $defaultLanguageId,
            'name' => $salesChannelDTO->getName(),
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        // Theme assigment
        $connection->insert('theme_sales_channel', [
            'sales_channel_id' => $id,
            'theme_id' => Uuid::fromHexToBytes($salesChannelDTO->getThemeId()),
        ]);
    }
}
