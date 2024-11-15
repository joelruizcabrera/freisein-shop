<?php

declare(strict_types=1);

namespace Shopware\Production\Logging\ErrorMail;

interface ErrorMailConfigInterface
{
    // Use variable %message% to get the error message.
    public function getSubject(): ?string;

    public function getFrom(): ?string;

    /**
     * @return string[]
     */
    public function getTo(): ?array;
}
