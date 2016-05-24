<?php

namespace Mealz\MealBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Mealz\MealBundle\Form\Type\DayType;

/**
 * form to add or edit a participant
 */
class WeekForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('days', CollectionType::class, array(
                'entry_type' => DayType::class
            ))
            ->add('disabled', CheckboxType::class, array(
                'required' => false,
                'attr' => array('class' => 'js-switch')
            ))
            ->add('Save', SubmitType::class, array(
                'attr' => array('class' => 'button')
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mealz\MealBundle\Entity\Week',
        ));
    }
}