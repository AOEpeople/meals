<?php

namespace App\Mealz\MealBundle\Form\MealAdmin;

use Doctrine\ORM\EntityManagerInterface;
use App\Mealz\MealBundle\Entity\Day;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Mealz\MealBundle\Entity\Meal;
use Doctrine\ORM\UnitOfWork;
use Symfony\Component\Validator\Constraints\Valid;

class DayForm extends AbstractType
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @inheritDoc
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'meals',
                CollectionType::class,
                [
                    'entry_type' => MealForm::class,
                    'allow_delete' => true,
                    'delete_empty' => true,
                    'constraints' => new Valid(),
                    'allow_add' => true,
                ]
            )
            ->add(
                'lockParticipationDateTime',
                DateTimeType::class,
                [
                    'required' => false,
                    'widget' => 'single_text',
                    'format' => 'YYYY-MM-dd HH:mm:ss',
                    'attr' => ['class' => 'hidden-form-field']
                ]
            )
            ->add(
                'enabled',
                CheckboxType::class,
                [
                    'required' => false,
                    'attr' => ['class' => 'js-switch'],
                ]
            );

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($builder) {
            /** @var Day $day */
            $day = $event->getData();

            $meals = $day->getMeals();

            foreach ($meals as $meal) {
                /** @var Meal $meal */
                if (null === $meal->getDish() &&
                    $this->entityManager->getUnitOfWork()->getEntityState($meal) == UnitOfWork::STATE_NEW
                ) {
                    $meals->removeElement($meal);
                }
            }

            $event->setData($day);
        });

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($builder) {
            $day = $event->getData();

            if (false === $day->getWeek()->isEnabled()) {
                $form = $event->getForm();
                $config = $form->get('enabled')->getConfig();
                $options = $config->getOptions();

                $form->add(
                    'enabled',
                    $config->getType()->getBlockPrefix(),
                    array_replace(
                        $options,
                        [
                            'disabled' => true
                        ]
                    )
                );
            }
        });
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Day::class,
        ]);
    }
}
