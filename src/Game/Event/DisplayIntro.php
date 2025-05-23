<?php

namespace App\Game\Event;

final class DisplayIntro extends AbstractEvent
{
    public function name(): string
    {
        return 'display_intro';
    }
}
