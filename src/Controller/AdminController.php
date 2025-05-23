<?php

namespace App\Controller;

use App\Game\Event\Broadcaster;
use App\Game\Event\ChangeStage;
use App\Game\Event\DisplayIntro;
use App\Game\Event\StartStage;
use App\Game\Game;
use App\Helper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    public function __construct(private readonly Broadcaster $broadcaster, private Game $game)
    {
    }

    #[Route('/admin', name: 'app_admin')]
    public function index(Request $request): Response
    {
        $game = new Game();

        $stageForm = $this->changeStageForm($game);
        $stageForm->handleRequest($request);

        if ($stageForm->isSubmitted() && $stageForm->isValid()) {
            $data = $stageForm->getData();

            if ($stageForm->get('change_stage')->isClicked()) {
                $this->broadcaster->send(new ChangeStage($data['stage']));
                $this->game->changeStage($data['stage']->number);
            }
            if ($stageForm->get('start_stage')->isClicked()) {
                $this->broadcaster->send(new StartStage($data['stage']));
                $this->game->startStage($data['stage']->number);
            }
            if ($stageForm->get('display_intro')->isClicked()) {
                $this->broadcaster->send(new DisplayIntro());
            }
        }

        return $this->render('admin.html.twig', [
            'stage' => $stageForm,
            'topics' => [...Game::TEAMS, Broadcaster::TOPIC]
        ]);
    }

    private function changeStageForm(Game $game): FormInterface
    {
        return $this->createFormBuilder()
            ->add('stage', ChoiceType::class, [
                'choices' => $game->stages,
                'choice_label' => 'label',
                'expanded' => true,
            ])
            ->add('change_stage', SubmitType::class)
            ->add('start_stage', SubmitType::class)
            ->add('display_intro', SubmitType::class)
            ->getForm();
    }
}
