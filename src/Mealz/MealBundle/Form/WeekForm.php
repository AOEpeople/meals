<?php

namespace Mealz\MealBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Mealz\MealBundle\Form\Type\DayType;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * form to add or edit a participant
 */
class WeekForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('days', CollectionType::class, array(
                'entry_type' => DayType::class,
                'constraints' => new Valid()
            ))
            ->add('enabled', CheckboxType::class, array(
                'required' => false,
                'attr' => array('class' => 'js-switch')
            ))
            ->add('Cancel', SubmitType::class, array(
                'attr' => array('class' => 'button')
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