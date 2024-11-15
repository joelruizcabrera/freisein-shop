<?php

declare(strict_types=1);

namespace HbH\ProjectConfig\Component\Config;

interface ConfigInterface
{
    public function isDisableSearchEngineIndexing(): bool;
}
