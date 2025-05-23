<?php

namespace App\Game\Event;

use App\Game\Stage;
use Symfony\Component\Serializer\Attribute\Groups;

abstract class AbstractStageEvent extends AbstractEvent
{
    #[Groups(["public"])]
    public int $stage;

    public function __construct(Stage $stage)
    {
        $this->stage = $stage->number;
    }
}
