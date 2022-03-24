<?php

namespace App\Mealz\MealBundle\Form\MealAdmin;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Form\Type\EntityHiddenType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MealForm extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dish', EntityHiddenType::class, [
                'class' => Dish::class,
            ])
            ->add('day', EntityHiddenType::class, [
                'class' => Day::class,
            ])
            ->add('participationLimit', IntegerType::class, [
                'required' => false,
                'empty_data' => '0',
                'attr' => ['class' => 'hidden-form-field participation-limit'],
            ]);

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
            if (null === $meal->getDateTime()) {
                $day = $event->getForm()->getParent()->getParent()->getData();
                $meal->setDay($day);
                $meal->setDateTime($day->getDateTime());
            }
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
//            'data_class' => Meal::class,
            'empty_data' => static function(FormInterface $form) {
                return new Meal(
                    $form->get('dish')->getData(),
                    $form->get('day')->getData()
                );
            },
            'error_bubbling' => false,
        ]);
    }
}
