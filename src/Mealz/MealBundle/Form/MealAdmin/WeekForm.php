<?php

namespace App\Mealz\MealBundle\Form\MealAdmin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * form to add or edit a participant.
 */
class WeekForm extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('days', CollectionType::class, [
                'entry_type' => DayForm::class,
                'constraints' => new Valid(),
            ])
            ->add('enabled', CheckboxType::class, [
                'required' => false,
                'attr' => ['class' => 'js-switch'],
            ])
            ->add('Cancel', SubmitType::class, [
                'label' => 'button.cancel',
                'translation_domain' => 'actions',
                'attr' => ['class' => 'button button-cancel'],
            ])
            ->add('Save', SubmitType::class, [
                'label' => 'button.save',
                'translation_domain' => 'actions',
                'attr' => ['class' => 'button'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => 'App\Mealz\MealBundle\Entity\Week',
        ]);
    }
}
