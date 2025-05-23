<?php

namespace App\Game;

final readonly class Stage
{
    public function __construct(public int $number, private Game $game)
    {
    }

    public function isOver(): bool
    {
        foreach($this->game->scores as $score) {
            if ($score >= 10) {
                return true;
            }
        }
        return false;
    }

    public function getWinner(): string|false
    {
        foreach($this->game->scores as $team => $score) {
            if ($score >= 10) {
                return $team;
            }
        }
        return false;
    }

    public function label(): string
    {
        return (string) $this->number;
    }
}
