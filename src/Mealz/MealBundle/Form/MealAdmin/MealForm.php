<?php

namespace Mealz\MealBundle\Form\MealAdmin;

use Mealz\MealBundle\Entity\Day;
use Mealz\MealBundle\Entity\Dish;
use Mealz\MealBundle\Entity\DishRepository;
use Mealz\MealBundle\Entity\Meal;
use Mealz\MealBundle\Entity\Week;
use Mealz\MealBundle\Form\Type\EntityHiddenType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MealForm
 * @package Mealz\MealBundle\Form\MealAdmin
 */
class MealForm extends AbstractType
{
    protected $dishRepository;

    /**
     * MealForm constructor.
     * @param DishRepository $dishRepository
     */
    public function __construct(DishRepository $dishRepository)
    {
        $this->dishRepository = $dishRepository;
    }

    /**
     * build the Form
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'dish',
                EntityHiddenType::class,
                array(
                    'class' => 'Mealz\MealBundle\Entity\Dish',
                )
            )
            ->add(
                'day',
                EntityHiddenType::class,
                array(
                    'class' => 'Mealz\MealBundle\Entity\Day',
                )
            )
            ->add(
                'participationLimit',
                IntegerType::class,
                array(
                    'attr' => array('class' => 'hidden-form-field participation-limit')
                )
            );

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($builder) {
                /** @var Meal $meal */
                $meal = $event->getData();

                /** just for data-prototype purposes */
                if ($meal === null) {
                    return;
                }

                /** @var Day $day */
                $day = $meal->getDay();
                /** @var Week $week */
                $week = $day->getWeek();

            if (false === $day->isEnabled() || false === $week->isEnabled()) {
                $form = $event->getForm();
                $config = $form->get('dish')->getConfig();
                $options = $config->getOptions();

                $form->add(
                    'dish',
                    $config->getType()->getName(),
                    array_replace(
                        $options,
                        [
                            'disabled' => true
                        ]
                    )
                );
            }
        });

        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) use ($builder) {
                /** @var Meal $meal */
                $meal = $event->getData();
                if ($meal->getDateTime() === null) {
                    if (null === $meal->getDay()) {
                        $day = $event->getForm()->getParent()->getParent()->getData();
                    } else {
                        $day = $meal->getDay();
                    }
                    $meal->setDay($day);
                    $meal->setDateTime($day->getDateTime());
                }
                if (null !== $meal->getDish()) {
                    $dishPrice = $meal->getDish()->getPrice();
                    $meal->setPrice($dishPrice);
                    $event->setData($meal);
                }
            }
        );
    }

    /**
     * configure the Options
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mealz\MealBundle\Entity\Meal',
            'error_bubbling' => false
        ));
    }

}