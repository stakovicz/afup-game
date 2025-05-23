<?php

namespace App\Game\Event;

use Symfony\Component\Serializer\Attribute\Groups;

abstract class AbstractEvent implements EventInterface
{
    #[Groups(["public"])]
    public function getName(): string
    {
        return $this->name();
    }
}
