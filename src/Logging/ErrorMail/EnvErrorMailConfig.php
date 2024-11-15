<?php

declare(strict_types=1);

namespace Shopware\Production\Logging\ErrorMail;

class EnvErrorMailConfig implements ErrorMailConfigInterface
{
    /**
     * @param ?string[] $to
     */
    public function __construct(
        private readonly ?string $subject,
        private readonly ?string $from,
        private readonly ?array $to
    ) {
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function getFrom(): ?string
    {
        return $this->from;
    }

    public function getTo(): ?array
    {
        return $this->to;
    }
}
