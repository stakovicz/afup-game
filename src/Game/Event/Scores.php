<?php

namespace App\Game\Event;

use Symfony\Component\Serializer\Attribute\Groups;

final class Scores extends AbstractEvent
{
    public function __construct(
        #[Groups(["public"])]
        public array $scores
    )
    {
    }

    public function name(): string
    {
        return 'scores';
    }
}
