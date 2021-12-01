<?php

namespace App\Mealz\MealBundle\Form\Guest;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvitationForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // passing by Day value to the DayForm in order to render only particular day
        $builder
            ->add('day', DayForm::class, [
                'data' => $options['data']->getDay(),
            ])
            ->add('profile', ProfileForm::class)
            ->add('save', SubmitType::class, [
                'label' => 'button.save',
                'translation_domain' => 'actions',
                'attr' => [
                    'class' => 'button small',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => 'App\Mealz\MealBundle\Entity\InvitationWrapper',
            'csrf_protection' => false,
        ]);
    }
}
