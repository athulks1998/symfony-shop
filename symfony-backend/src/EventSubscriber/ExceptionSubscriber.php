<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Exception\InsufficientStockException;
use App\Exception\InvalidStatusTransitionException;
use App\Exception\ResourceNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Global exception handler — converts domain exceptions to consistent JSON error responses.
 * Keeps controller code free of try/catch boilerplate.
 */
class ExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 10],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        [$statusCode, $errorCode] = match (true) {
            $exception instanceof ResourceNotFoundException        => [Response::HTTP_NOT_FOUND,            'NOT_FOUND'],
            $exception instanceof InsufficientStockException       => [Response::HTTP_CONFLICT,             'INSUFFICIENT_STOCK'],
            $exception instanceof InvalidStatusTransitionException => [Response::HTTP_UNPROCESSABLE_ENTITY, 'INVALID_TRANSITION'],
            default                                                => [Response::HTTP_INTERNAL_SERVER_ERROR, 'INTERNAL_ERROR'],
        };

        $response = new JsonResponse([
            'error'   => $errorCode,
            'message' => $exception->getMessage(),
        ], $statusCode);

        $event->setResponse($response);
    }
}
