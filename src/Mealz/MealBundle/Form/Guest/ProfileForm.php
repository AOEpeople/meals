<?php

namespace App\Mealz\MealBundle\Form\Guest;

use App\Mealz\UserBundle\Entity\Profile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileForm extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'form.placeholder.name',
                ],
                'translation_domain' => 'general',
            ])
            ->add('firstName', TextType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'form.placeholder.first_name',
                ],
                'translation_domain' => 'general',
            ])
            ->add('company', TextType::class, [
                'required' => false,
                'empty_data' => '',
                'attr' => [
                    'placeholder' => 'form.placeholder.company',
                ],
                'translation_domain' => 'general',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Profile::class,
        ]);
    }
}
