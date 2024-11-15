<?php

declare(strict_types=1);

namespace HbH\ProjectConfig\Migration\DTO;

interface SalesChannelDTOInterface
{
    public const TYPE_STOREFRONT = 'storefront';
    public const TYPE_HEADLESS = 'headless';
    public const VALID_TYPES = [self::TYPE_HEADLESS, self::TYPE_STOREFRONT];

    public function getId(): string;

    public function getTypeId(): string;

    public function getDefaultLanguageId(): string;

    public function getDefaultCurrencyId(): string;

    public function getDefaultPaymentMethodId(): string;

    public function getDefaultShippingMethodId(): string;

    public function getDefaultCountryId(): string;

    public function getNavigationCategoryId(): string;

    public function getNavigationCategoryVersionId(): string;

    public function isActive(): bool;

    public function getDefaultCustomerGroupId(): string;

    public function getThemeId(): string;

    public function getName(): string;
}
