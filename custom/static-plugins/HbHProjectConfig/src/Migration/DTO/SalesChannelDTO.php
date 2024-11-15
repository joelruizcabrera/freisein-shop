<?php

declare(strict_types=1);

namespace HbH\ProjectConfig\Migration\DTO;

use Doctrine\DBAL\Connection;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Checkout\Shipping\ShippingMethodEntity;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\EntityNotFoundException;
use Shopware\Core\System\Country\CountryEntity;
use Shopware\Storefront\Theme\ThemeEntity;

class SalesChannelDTO implements SalesChannelDTOInterface
{
    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly string $type,
        private readonly Connection $connection
    )
    {
        if (!\in_array($type, self::VALID_TYPES, true)) {
            throw new \Exception(sprintf('Invalid type "%s". Valid types are: %s', $type, implode(',', self::VALID_TYPES)));
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTypeId(): string
    {
        return ($this->type === self::TYPE_HEADLESS) ? Defaults::SALES_CHANNEL_TYPE_API : Defaults::SALES_CHANNEL_TYPE_STOREFRONT;
    }

    public function getDefaultLanguageId(): string
    {
        return Defaults::LANGUAGE_SYSTEM;
    }

    public function getDefaultCurrencyId(): string
    {
        return Defaults::CURRENCY;
    }

    public function getDefaultPaymentMethodId(): string
    {
        return
            $this->connection->executeQuery('SELECT HEX(id) FROM payment_method WHERE active = 1 ORDER BY `position`')->fetchOne() ?:
            throw new EntityNotFoundException(PaymentMethodEntity::class, 'First active');
    }

    public function getDefaultShippingMethodId(): string
    {
        return
            $this->connection->executeQuery('SELECT HEX(id) FROM shipping_method WHERE active = 1')->fetchOne() ?:
            throw new EntityNotFoundException(ShippingMethodEntity::class, 'First active');
    }

    public function getDefaultCountryId(): string
    {
        return
            $this->connection->executeQuery('SELECT HEX(id) FROM country WHERE active = 1 ORDER BY `position`')->fetchOne() ?:
            throw new EntityNotFoundException(CountryEntity::class, 'First active');
    }

    public function getNavigationCategoryId(): string
    {
        return
            $this->connection->executeQuery('SELECT HEX(id) FROM category ORDER BY `created_at`')->fetchOne() ?:
            throw new EntityNotFoundException(CategoryEntity::class, 'First created');
    }

    public function getNavigationCategoryVersionId(): string
    {
        return Defaults::LIVE_VERSION;
    }

    public function isActive(): bool
    {
        return true;
    }

    public function getDefaultCustomerGroupId(): string
    {
        //        return Defaults::FALLBACK_CUSTOMER_GROUP;
        return 'cfbd5018d38d41d8adca10d94fc8bdd6';
    }

    public function getThemeId(): string
    {
        return
            $this->connection->executeQuery('SELECT HEX(id) FROM theme WHERE active = 1 ORDER BY `created_at`')->fetchOne() ?:
            throw new EntityNotFoundException(ThemeEntity::class, 'First created and active');
    }

    public function getName(): string
    {
        return $this->name;
    }
}
