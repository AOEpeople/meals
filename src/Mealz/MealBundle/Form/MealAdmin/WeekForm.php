<?php

namespace App\Mealz\MealBundle\Form\MealAdmin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * form to add or edit a participant
 */
class WeekForm extends AbstractType
{
    /**
     * build the Form
     * @param FormBuilderInterface $builder
     * @param array $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('days', CollectionType::class, array(
                'entry_type' => DayForm::class,
                'constraints' => new Valid()
            ))
            ->add('enabled', CheckboxType::class, array(
                'required' => false,
                'attr' => array('class' => 'js-switch')
            ))
            ->add('Cancel', SubmitType::class, array(
                'label' => 'button.cancel',
                'translation_domain' => 'actions',
                'attr' => array('class' => 'button button-cancel')
            ))
            ->add('Save', SubmitType::class, array(
                'label' => 'button.save',
                'translation_domain' => 'actions',
                'attr' => array('class' => 'button')
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Mealz\MealBundle\Entity\Week',
        ));
    }
}
