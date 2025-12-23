<?php

namespace App\EventListener;

use App\Entity\LogAction;
use App\Entity\Movie;
use App\Event\EntitySavedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: EntitySavedEvent::NAME)]
class EntitySavedListener
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(EntitySavedEvent $event): void
    {
        $entity = $event->getEntity();
        $action = $event->getAction();

        // Créer un log d'action
        $logAction = new LogAction();
        $logAction->setEntityType(get_class($entity));
        $logAction->setAction($action);

        // Récupérer l'ID si l'entité en a un
        if (method_exists($entity, 'getId')) {
            $logAction->setEntityId($entity->getId());
        }

        // Ajouter des métadonnées spécifiques si c'est un Movie
        if ($entity instanceof Movie) {
            $logAction->setMetadata([
                'name' => $entity->getName(),
                'duration' => $entity->getDuration(),
                'releaseDate' => $entity->getReleaseDate()?->format('Y-m-d'),
            ]);
        }

        $this->entityManager->persist($logAction);
        $this->entityManager->flush();
    }
}
