<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Form\MealAdmin;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\EventParticipation;
use App\Mealz\MealBundle\Form\Type\EntityHiddenType;
use App\Mealz\MealBundle\Repository\EventParticipationRepositoryInterface;
use App\Mealz\MealBundle\Repository\EventRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class DayForm extends AbstractType
{
    protected EntityManagerInterface $entityManager;

    protected EventRepositoryInterface $eventRepository;

    protected EventParticipationRepositoryInterface $eventPartRepository;

    public function __construct(EntityManagerInterface $entityManager, EventRepositoryInterface $eventRepository, EventParticipationRepositoryInterface $eventPartRepository)
    {
        $this->entityManager = $entityManager;
        $this->eventRepository = $eventRepository;
        $this->eventPartRepository = $eventPartRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('meals', CollectionType::class, [
                'entry_type' => MealForm::class,
                'allow_delete' => true,
                'delete_empty' => true,
                'constraints' => new Valid(),
                'allow_add' => true,
            ])
            ->add('event', EntityHiddenType::class, [
                'class' => EventParticipation::class,
                'empty_data' => null,
                'required' => false,
            ])
            ->add('lockParticipationDateTime', DateTimeType::class, [
                'required' => false,
                'widget' => 'single_text',
                'format' => 'YYYY-MM-dd HH:mm:ss',
                'attr' => ['class' => 'hidden-form-field'],
            ])
            ->add('enabled', CheckboxType::class, [
                'required' => false,
                'attr' => ['class' => 'js-switch'],
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event) {
            $day = $event->getData();

            if (true === $day->getWeek()->isEnabled()) {
                return;
            }

            // disable day meals and meal enable/disable option if meal week is disabled
            $form = $event->getForm();
            $opts = $form->get('enabled')->getConfig()->getOptions();
            $opts['disabled'] = true;

            $form->add('enabled', CheckboxType::class, $opts);
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $formEvent) {
            /** @var array $data client submitted meal data */
            $data = $formEvent->getData();

            if (!empty($data['event'])) {
                $day = $formEvent->getForm()->getData();
                $event = $this->eventRepository->find($data['event']);
                $eventParticipation = $this->eventPartRepository->findByEventAndDay($day, $event);
                if (null === $eventParticipation) {
                    $eventParticipation = new EventParticipation($day, $event);
                    $this->eventPartRepository->add($eventParticipation);
                }
                $data['event'] = $eventParticipation->getId();
                $formEvent->setData($data);
            }
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Day::class,
        ]);
    }
}
