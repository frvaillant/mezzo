<?php

namespace App\EventListener;

use App\Security\Exception\UnlockRequiredException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Routing\RouterInterface;

class UnlockRedirectListener
{
    public function __construct(private RouterInterface $router) {}

    /**
     * @throws \ReflectionException
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $class = new \ReflectionClass($exception::class);

        if ($class->hasProperty('statusCode') && $exception?->getStatusCode() === Response::HTTP_UNAUTHORIZED) {
            $response = new RedirectResponse($this->router->generate('app_login'));
            $event->setResponse($response);
        }
    }
}

