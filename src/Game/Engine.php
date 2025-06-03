<?php

namespace App\Game;


use App\Entity\Game;
use App\Entity\Player;
use App\Entity\Point;
use App\Repository\GameRepository;
use App\Repository\PointRepository;
use Doctrine\ORM\EntityManagerInterface;

class Engine
{
    const string TEAM_RED = 'red';
    const string TEAM_BLUE = 'blue';
    const array TEAMS = [
        self::TEAM_RED, self::TEAM_BLUE
    ];
    public array $stages = [];
    public Stage $stage;
    private readonly Game $game;
    public array $players = [];

    public function __construct(private readonly GameRepository $gameRepository,
                                private readonly PointRepository $pointRepository,
                                private readonly EntityManagerInterface $entityManager)
    {
        $this->stages = [
            new Stage(0, $this),
            new Stage(1, $this),
            new Stage(2, $this),
            new Stage(3, $this),
            new Stage(4, $this),
        ];

        $this->game = $this->gameRepository->findOneBy([], ['id' => 'desc']) ?? new Game();
        $entityManager->persist($this->game);

        $this->changeStage($this->game->getStage());
    }

    public function changeStage(int $stageNumber): void
    {
        foreach ($this->stages as $stage) {
            if ($stage->number === $stageNumber) {
                $this->stage = $stage;
                $this->game->setStage($stage->number);
                $this->entityManager->flush();

                return;
            }
        }
    }

    public function startStage(int $stageNumber): void
    {

    }

    public function addPoint(Player $player): array
    {
        $point = new Point();
        $point->setTeam($player->getTeam());
        $point->setStage($this->game->getStage());
        $point->setPlayer($player);
        $point->setValue(1);

        $this->entityManager->persist($point);
        $this->entityManager->flush();

        return $this->pointRepository->scores($this->game->getStage());
    }

    public function removePoint(Player $player): array
    {
        $point = new Point();
        $point->setTeam($player->getTeam());
        $point->setStage($this->game->getStage());
        $point->setPlayer($player);
        $point->setValue(-1);

        $this->entityManager->persist($point);
        $this->entityManager->flush();

        $scores = $this->pointRepository->scores($this->game->getStage());
        if ($scores[$player->getTeam()] >= 1) {
            $scores[$player->getTeam()]--;
        }

        return $scores;
    }

    public function stageIsOver(): bool
    {
        return $this->stage->isOver();
    }

    public function getWinner(): string|false
    {
        return $this->stage->getWinner();
    }

    public function saveState(): void
    {
        $this->entityManager->persist($this->game);
        $this->entityManager->flush();
    }

    public function generatePlayerKey(): string
    {
        $watchdog = 1000;
        do {
            $key = strtoupper(bin2hex(random_bytes(2)));
            if ($watchdog-- <= 0) {
                throw new \RuntimeException("Cannot generate key");
            }
        } while (in_array($key, $this->players, true));

        return substr($key, 0, 3);
    }

    public function addPlayer(string $key, string $team): void
    {
        $player = new Player();
        $player->setKey($key);
        $player->setTeam($team);

        $this->entityManager->persist($player);

        $this->game->addPlayer($player);
    }

    public function getScores(): array
    {
        return $this->pointRepository->scores($this->game->getStage());
    }
}
