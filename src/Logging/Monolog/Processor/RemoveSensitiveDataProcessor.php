<?php

declare(strict_types=1);

namespace Shopware\Production\Logging\Monolog\Processor;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class RemoveSensitiveDataProcessor implements ProcessorInterface
{
    private const SENSITIVE_KEYS = ['password'];

    public function __invoke(LogRecord $record): LogRecord
    {
        return $record->with(context: $this->removeSensitiveData($record->context));
    }

    private function removeSensitiveData(array $context): array
    {
        foreach ($context as $key => $item) {
            if (\is_array($item)) {
                $context[$key] = $this->removeSensitiveData($item);
            } elseif (\in_array(strtolower((string) $key), self::SENSITIVE_KEYS, true)) {
                $context[$key] = '****';
            }
        }

        return $context;
    }
}
