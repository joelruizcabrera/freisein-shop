<?php

declare(strict_types=1);

namespace Shopware\Production\Logging\ErrorMail;

use Symfony\Component\Mime\Email;

/**
 * Used as a closure in Symfony\Bridge\Monolog\Handler\MailerHandler::buildMessage()
 * For TypeHinting purpose this class must extend Email.
 */
class ErrorMail extends Email
{
    public function __construct(private readonly ErrorMailConfigInterface $errorMailConfig)
    {
        parent::__construct();
    }

    public function create(): Email
    {
        if (!$this->errorMailConfig->getSubject()) {
            throw new \InvalidArgumentException('Could not resolve message as instance of Email or a callable returning it.');
        }

        return (new Email())
            ->subject($this->errorMailConfig->getSubject())
            ->from($this->errorMailConfig->getFrom())
            ->to(...$this->errorMailConfig->getTo())
            ->priority(Email::PRIORITY_HIGH)
        ;
    }
}
