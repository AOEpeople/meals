<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Form\MealAdmin;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Event;
use App\Mealz\MealBundle\Form\Type\EntityHiddenType;
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

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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
                'class' => Event::class,
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
