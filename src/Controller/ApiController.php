<?php

namespace App\Controller;

use App\Game\Event\Broadcaster;
use App\Game\Event\Scores;
use App\Game\Event\Winner;
use App\Game\Game;
use App\Helper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api', name: 'api_')]
final class ApiController extends AbstractController
{
    public function __construct(
        private readonly HubInterface $hub,
        private readonly Broadcaster $broadcaster,
        private readonly Game $game,
    )
    {
    }

    #[Route('/players', name: 'players')]
    public function players(): Response
    {
        return new JsonResponse($this->getPlayers());
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): Response
    {
        $players = $this->getPlayers();
        if ($request->getMethod() === 'GET') {
            return new JsonResponse($players);
        }

        $team = array_search(min($players), $players, true);
        $gameUrl = Helper::buildUrlForTopics($this->hub->getPublicUrl(), [$team, Broadcaster::TOPIC]);

        return new JsonResponse([
            'team' => $team,
            'game_url' => $gameUrl,
            'play_url' => $this->generateUrl('api_play')
        ]);
    }

    #[Route('/play', name: 'play', methods: ['POST'])]
    public function play(Request $request): Response
    {
        if ($this->game->stageIsOver()) {
            return new JsonResponse([
                'status' => 'ok'
            ]);
        }

        $data = $request->toArray();
        switch ($data['action']) {
            case "add":
                $scores = $this->game->addPoint($data['team']);
                $this->broadcaster->send(new Scores($scores));
                break;
            case "remove":
                $scores = $this->game->removePoint($data['team']);
                $this->broadcaster->send(new Scores($scores));
                break;
        }

        if ($this->game->stageIsOver()) {
            $this->broadcaster->send(new Winner($this->game->getWinner(), $this->game->scores));
        }

        return new JsonResponse([
            'status' => 'ok'
        ]);
    }

    private function getPlayers(): array
    {
        $data = $this->broadcaster->getSubscriptions();

        $teams = array_fill_keys(Game::TEAMS, 0);
        foreach($data['subscriptions'] as $subscription) {
            if (!isset($teams[$subscription['topic']])) {
                continue;
            }
            $teams[$subscription['topic']]++;
        }

        return $teams;
    }
}
