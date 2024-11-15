<?php

declare(strict_types=1);

namespace Shopware\Production\Subscriber;

use Shopware\Core\Framework\Routing\KernelListenerPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Request;

class BasicAuthSubscriber implements EventSubscriberInterface
{
    private const SCOPE_STOREFRONT = 'storefront';
    private const SCOPE_ADMINISTRATION = 'administration';
    private const SCOPE_API = 'api';
    private const DEFAULT_SECURED_SCOPES = [self::SCOPE_STOREFRONT, self::SCOPE_ADMINISTRATION];

    public function __construct(
        private readonly ?string $basicAuthUser,
        private readonly ?string $basicAuthPassword,
        private readonly ?array $basicAuthScopes
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => [
                'onKernelController',
                KernelListenerPriorities::KERNEL_CONTROLLER_EVENT_PRIORITY_AUTH_VALIDATE_PRE
            ]
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $request = $event->getRequest();

        if (!$this->basicAuthUser || !$this->basicAuthPassword || !$this->isSecuredScope($request)) {
            return;
        }

        $enteredUser = $request->server->get('PHP_AUTH_USER');
        $enteredPassword = $request->server->get('PHP_AUTH_PW');

        if (
            !$enteredUser ||
            !$enteredPassword ||
            $this->basicAuthUser !== $enteredUser ||
            !password_verify($enteredPassword, password_hash($this->basicAuthPassword, PASSWORD_DEFAULT))
        ) {
            header('WWW-Authenticate: Basic realm="Secured Area"');
            header('HTTP/1.0 401 Unauthorized');
            exit;
        }
    }

    private function isSecuredScope(Request $request): bool
    {
        $routeScopes = $request->attributes->get('_routeScope', []);
        $securedScopes = $this->basicAuthScopes ?? self::DEFAULT_SECURED_SCOPES;

        $isSecuredScope = count(array_intersect($routeScopes, $securedScopes)) > 0;

        // Administration scope require additional checks
        if ($isSecuredScope && in_array(self::SCOPE_ADMINISTRATION, $routeScopes, true)) {
            // Skip if user is logged in to avoid endless loop because required server variables are not passed
            if ($request->cookies->get('bearerAuth')) {
                $isSecuredScope = false;
            }
            // Skip if its a request for an administration "api" route
            if (str_starts_with($request->server->get('REQUEST_URI'), '/' . self::SCOPE_API)) {
                $isSecuredScope = false;
            }
        }

        return $isSecuredScope;
    }
}
