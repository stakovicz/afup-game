<?php

namespace App\Game\Event;

final class StartStage extends AbstractStageEvent
{
    public function name(): string
    {
        return 'start_stage';
    }
}
