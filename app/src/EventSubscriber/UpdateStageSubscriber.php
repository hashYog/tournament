<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\UpdateStageEvent;
use App\Repository\TournamentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class UpdateStageSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private TournamentRepository $tournamentRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UpdateStageEvent::class => 'onEndGroupStage',
        ];
    }

    public function onEndGroupStage(UpdateStageEvent $event): void
    {
        $tournament = $this->tournamentRepository->get();
        $tournament->updateStage($event->stage);

        $this->entityManager->flush();
    }
}
