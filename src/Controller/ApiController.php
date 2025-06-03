<?php

namespace App\Controller;

use App\Entity\Player;
use App\Game\Event\Broadcaster;
use App\Game\Event\Scores;
use App\Game\Event\Winner;
use App\Game\Engine;
use App\Helper;
use App\Repository\PlayerRepository;
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
        private readonly HubInterface     $hub,
        private readonly Broadcaster      $broadcaster,
        private readonly Engine           $engine,
        private readonly PlayerRepository $playerRepository,
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

        $key = ''; //$request->getSession()->get('player');
        $team = ''; //$request->getSession()->get('team');

        if (!$key && !$team) {
            $team = array_search(min($players), $players, true);
            $key = $this->engine->generatePlayerKey();
            $this->engine->addPlayer($key, $team);
            $this->engine->saveState();

            $request->getSession()->set('player', $key);
            $request->getSession()->set('team', $team);
        }

        $gameUrl = Helper::buildUrlForTopics($this->hub->getPublicUrl(), [$team, Broadcaster::TOPIC]);

        return new JsonResponse([
            'team' => $team,
            'game_url' => $gameUrl,
            'key' => $key,
            'play_url' => $this->generateUrl('api_play')
        ]);
    }

    #[Route('/play', name: 'play', methods: ['POST'])]
    public function play(Request $request): Response
    {
        if ($this->engine->stageIsOver()) {
            return new JsonResponse([
                'status' => 'ok',
                'stage' => 'over'
            ]);
        }
        $data = $request->toArray();

        $player = $this->playerRepository->findOneBy(['key' => $data['key']]);
        if (!$player instanceof Player) {
            throw $this->createNotFoundException(sprintf('Player with key "%s" not found.', $data['key']));
        }

        $scores = [];
        switch ($data['action']) {
            case "add":
                $scores = $this->engine->addPoint($player);
                break;
            case "remove":
                $scores = $this->engine->removePoint($player);
                break;
        }

        if ($this->engine->stageIsOver()) {
            $this->broadcaster->send(new Winner($this->engine->getWinner(), $scores));
        } else {
            $this->broadcaster->send(new Scores($scores));
        }

        return new JsonResponse([
            'status' => 'ok',
            'score' => $scores
        ]);
    }

    private function getPlayers(): array
    {
        $data = $this->broadcaster->getSubscriptions();

        $teams = array_fill_keys(Engine::TEAMS, 0);
        foreach ($data['subscriptions'] as $subscription) {
            if (!isset($teams[$subscription['topic']])) {
                continue;
            }
            $teams[$subscription['topic']]++;
        }

        return $teams;
    }
}
