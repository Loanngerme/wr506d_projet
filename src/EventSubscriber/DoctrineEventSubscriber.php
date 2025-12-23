<?php

namespace App\EventSubscriber;

use App\Entity\Movie;
use App\Event\EntitySavedEvent;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
class DoctrineEventSubscriber
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->handleEvent($args, 'persist');
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->handleEvent($args, 'update');
    }

    private function handleEvent(LifecycleEventArgs $args, string $action): void
    {
        $entity = $args->getObject();

        // Ne déclencher l'événement que pour l'entité Movie
        if ($entity instanceof Movie) {
            $event = new EntitySavedEvent($entity, $action);
            $this->eventDispatcher->dispatch($event, EntitySavedEvent::NAME);
        }
    }
}
