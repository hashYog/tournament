<?php

namespace App\EventSubscriber;

use App\Exception\NotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $code = Response::HTTP_BAD_REQUEST;

        if ($exception instanceof NotFoundException) {
            $code = Response::HTTP_NOT_FOUND;
        }

        $event->setResponse(
            new JsonResponse(
                [
                    'error' => [
                        'code' => $code,
                        'message' => $exception->getMessage(),
                    ]
                ],
                $code
            ),
        );
    }
}