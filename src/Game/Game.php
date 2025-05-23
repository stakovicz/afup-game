<?php

namespace App\Game;


class Game
{
    const string TEAM_RED = 'red';
    const string TEAM_BLUE = 'blue';
    const array TEAMS = [
        self::TEAM_RED, self::TEAM_BLUE
    ];
    public array $stages = [];
    public Stage $stage;
    public array $scores;

    public function __construct()
    {
        $this->stages = [
            new Stage(1, $this),
            new Stage(2, $this),
            new Stage(3, $this),
            new Stage(4, $this),
        ];
        $this->resetScores();
    }

    public function changeStage(int $stageNumber): void
    {
        foreach ($this->stages as $stage) {
            if ($stage->number === $stageNumber) {
                $this->stage = $stage;
            }
        }
    }

    public function startStage(int $stageNumber): void
    {
        $this->resetScores();
        $this->saveState();
    }

    public function addPoint(string $team): array
    {
        if (!isset($this->scores[$team])) {
            throw new \RuntimeException("Team $team doesn't exist");
        }

        $this->readState();

        if ($this->stage->isOver()) {
            return $this->scores;
        }

        $this->scores[$team]++;
        $this->saveState();

        return $this->scores;
    }

    public function removePoint(string $team): array
    {
        if (!isset($this->scores[$team])) {
            throw new \RuntimeException("Team $team doesn't exist");
        }

        $this->readState();

        if ($this->stage->isOver()) {
            return $this->scores;
        }

        if ($this->scores[$team] >= 1) {
            $this->scores[$team]--;
        }

        $this->saveState();

        return $this->scores;
    }

    public function stageIsOver(): bool
    {
        $this->readState();
        return $this->stage->isOver();
    }

    public function getWinner(): string|false
    {
        $this->readState();
        return $this->stage->getWinner();
    }

    public function saveState(): void
    {
        file_put_contents($this->statePath(), json_encode([
            'scores' => $this->scores,
            'stage_number' => $this->stage->number ?? 1,
        ], JSON_THROW_ON_ERROR));
    }

    public function readState(): void
    {
        try {
            $state = file_get_contents($this->statePath());
        } catch (\Throwable) {
            return;
        }
        if (!$state) {
            return;
        }
        $state = json_decode($state, true, 512, JSON_THROW_ON_ERROR);
        $this->changeStage($state['stage_number']);
        $this->scores = $state['scores'];
    }

    private function statePath(): string
    {
        return dirname(__FILE__, 3).'/var/game.state.json';
    }

    private function resetScores(): void
    {
        $this->scores = array_fill_keys(self::TEAMS, 0);
    }
}
