<?php

namespace App\Controller;

use App\Game\Event\Broadcaster;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GameController extends AbstractController
{
    #[Route('/game', name: 'app_game')]
    public function index(): Response
    {
        return $this->render('game.html.twig', [
            'topics' => [Broadcaster::TOPIC]
        ]);
    }
}
