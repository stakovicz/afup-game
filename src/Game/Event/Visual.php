<?php

namespace App\Game\Event;

use Symfony\Component\Serializer\Attribute\Groups;

final class Visual extends AbstractEvent
{
    public function __construct(
        #[Groups(["public"])]
        public string $color = '',
        #[Groups(["public"])]
        public string $text = '',
        #[Groups(["public"])]
        public string $image = '',
        #[Groups(["public"])]
        public string $effect = ''
    )
    {
    }
    public function name(): string
    {
        return 'visual';
    }
}
