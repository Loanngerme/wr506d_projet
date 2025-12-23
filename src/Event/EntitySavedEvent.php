<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class EntitySavedEvent extends Event
{
    public const NAME = 'entity.saved';

    private object $entity;
    private string $action;

    public function __construct(object $entity, string $action = 'persist')
    {
        $this->entity = $entity;
        $this->action = $action;
    }

    public function getEntity(): object
    {
        return $this->entity;
    }

    public function getAction(): string
    {
        return $this->action;
    }
}
