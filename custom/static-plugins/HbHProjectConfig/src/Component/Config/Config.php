<?php

declare(strict_types=1);

namespace HbH\ProjectConfig\Component\Config;

use Shopware\Core\System\SystemConfig\SystemConfigService;

class Config implements ConfigInterface
{
    /** @var array<string, mixed> */
    private array $pluginConfig;

    public function __construct(
        SystemConfigService $configService,
        string $namespace
    )
    {
        /** @phpstan-ignore-next-line */
        $this->pluginConfig = $configService->get($namespace) ?? [];
    }

    public function isDisableSearchEngineIndexing(): bool
    {
        return $this->pluginConfig['disableSearchEngineIndexing'] ?? false;
    }
}
