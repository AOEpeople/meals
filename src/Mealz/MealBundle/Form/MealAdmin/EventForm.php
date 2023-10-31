<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Form\MealAdmin;

use App\Mealz\MealBundle\Entity\EventParticipation;
use App\Mealz\MealBundle\Form\Type\EntityHiddenType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventForm extends AbstractType
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // we take day value from parent day form; see configureOptions() method below.
        $builder
            ->add('event', EntityHiddenType::class, [
                'class' => EventParticipation::class,
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $formEvent) {
            /** @var Event|null $event */
            $event = $formEvent->getData();

            if (null === $event) {
                return;
            }

            $form = $formEvent->getForm();
            $config = $form->get('event')->getConfig();
            $opts = $config->getOptions();
            $opts['attr'] = ['readonly' => 'readonly'];
            $form->add('event', EntityHiddenType::class, $opts);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EventParticipation::class,
            'empty_data' => static function (FormInterface $form) {
                $event = $form->get('event')->getData();

                return $event;
            },
            'error_bubbling' => false,
        ]);
    }
}
