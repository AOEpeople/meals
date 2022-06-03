<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Form\MealAdmin;

use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\NullDish;
use App\Mealz\MealBundle\Form\Type\EntityHiddenType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MealForm extends AbstractType implements DataMapperInterface
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // we take day value from parent day form; see configureOptions() method below.
        $builder
            ->add('dish', EntityHiddenType::class, [
                'class' => Dish::class,
            ])
            ->add('participationLimit', IntegerType::class, [
                'required' => false,
                'empty_data' => '0',
                'attr' => ['class' => 'hidden-form-field participation-limit'],
            ])
            ->setDataMapper($this);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event) {
            /** @var Meal|null $meal */
            $meal = $event->getData();
            if (null === $meal) {
                return;
            }

            $day = $meal->getDay();
            $week = $day->getWeek();

            if (false === $day->isEnabled() || false === $week->isEnabled()) {
                $form = $event->getForm();
                $config = $form->get('dish')->getConfig();
                $opts = $config->getOptions();
                $opts['attr'] = ['readonly' => 'readonly'];

                $form->add('dish', EntityHiddenType::class, $opts);
            }
        });

        $builder->addEventListener(FormEvents::SUBMIT, static function (FormEvent $event) {
            /** @var Meal $meal */
            $meal = $event->getData();
            $dishPrice = $meal->getDish()->getPrice();
            $meal->setPrice($dishPrice);
            $event->setData($meal);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Meal::class,
            'empty_data' => static function (FormInterface $form) {
                $dish = $form->get('dish')->getData();
                $day = $form->getParent()->getParent()->getData();

                return new Meal($dish, $day);
            },
            'error_bubbling' => false,
        ]);
    }

    public function mapDataToForms($viewData, $forms): void
    {
        // there is no data yet, so nothing to pre-populate
        if (null === $viewData) {
            return;
        }

        if (!$viewData instanceof Meal) {
            throw new UnexpectedTypeException($viewData, Meal::class);
        }

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        // initialize form field values
        $forms['dish']->setData($viewData->getDish());
        $forms['participationLimit']->setData($viewData->getParticipationLimit());
    }

    public function mapFormsToData($forms, &$viewData): void
    {
        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);
        $dish = $forms['dish']->getData();
        if (null === $dish) {
            $dish = new NullDish();
        }

        $meal = $forms['dish']->getParent()->getData();
        if (null === $meal) {   // new meal
            $day = $forms['dish']->getParent()->getParent()->getParent()->getData();
            $meal = new Meal($dish, $day);
        } else {
            $meal->setDish($dish);
        }

        $viewData = $meal;
    }
}
