<?php

namespace App\Controller;

use App\Game\Event\Broadcaster;
use App\Game\Event\ChangeStage;
use App\Game\Event\DisplayIntro;
use App\Game\Event\StartStage;
use App\Game\Engine;
use App\Game\Event\Visual;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    public function __construct(private readonly Broadcaster $broadcaster,
                                private readonly Engine $engine)
    {
    }

    #[Route('/admin', name: 'app_admin')]
    public function index(Request $request): Response
    {
        $stageForm = $this->changeStageForm();
        $stageForm->handleRequest($request);
        if ($stageForm->isSubmitted() && $stageForm->isValid()) {
            $data = $stageForm->getData();

            if ($stageForm->get('change_stage')->isClicked()) {
                $this->broadcaster->send(new ChangeStage($data['stage']));
                $this->engine->changeStage($data['stage']->number);
            }
            if ($stageForm->get('start_stage')->isClicked()) {
                $this->broadcaster->send(new StartStage($data['stage']));
                $this->engine->startStage($data['stage']->number);
            }
            if ($stageForm->get('display_intro')->isClicked()) {
                $this->broadcaster->send(new DisplayIntro());
            }
        }

        $visualForm = $this->visualForm();
        $visualForm->handleRequest($request);
        if ($visualForm->isSubmitted() && $visualForm->isValid()) {
            $data = $visualForm->getData();
            if ($visualForm->get('random')->isClicked()) {
                $this->broadcaster->send(new Visual(color: sprintf('#%06X', random_int(0, 0xFFFFFF))));
            } else {
                $this->broadcaster->send(new Visual(...$data));
            }
        }

        return $this->render('admin.html.twig', [
            'stage' => $stageForm,
            'topics' => [...Engine::TEAMS, Broadcaster::TOPIC],
            'visual' => $visualForm
        ]);
    }

    private function changeStageForm(): FormInterface
    {
        return $this->createFormBuilder()
            ->add('stage', ChoiceType::class, [
                'choices' => $this->engine->stages,
                'choice_label' => 'label',
                'expanded' => true,
                'data' => $this->engine->stage,
            ])
            ->add('change_stage', SubmitType::class)
            ->add('start_stage', SubmitType::class)
            ->add('display_intro', SubmitType::class)
            ->getForm();
    }

    private function visualForm(): FormInterface
    {
        return $this->createFormBuilder()
            ->add('color', ColorType::class)
            ->add('text', TextType::class, ['required' => false, 'empty_data' => ''])
            ->add('image', TextType::class, ['required' => false, 'empty_data' => ''])
            ->add('send', SubmitType::class)
            ->add('random', SubmitType::class)
            ->getForm();
    }
}
