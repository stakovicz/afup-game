<?php

namespace App\Game\Event;

final class ChangeStage extends AbstractStageEvent
{
    public function name(): string
    {
        return 'change_stage';
    }
}
