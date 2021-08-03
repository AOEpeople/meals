<?php

namespace Mealz\MealBundle\Form\Guest;

use Mealz\MealBundle\Form\Type\DayType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * form to show invitation for guest
 */
class DayForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('meals', EntityType::class, array(
                'class' => 'Mealz\MealBundle\Entity\Meal',
                'query_builder' => function ($er) use ($options) {
                    return $er->createQueryBuilder('i')
                    ->where('i.day = :day')
                    ->setParameter('day', $options['data']->getId());
                },
                'expanded' => true,
                'multiple' => true,
                'mapped' => false,
                'choice_label' => false
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mealz\MealBundle\Entity\Day'
        ));
    }
}
