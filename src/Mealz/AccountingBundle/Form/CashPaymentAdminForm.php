<?php

namespace App\Mealz\AccountingBundle\Form;

use App\Mealz\MealBundle\Form\DataTransformer\ProfileToStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\NumberToLocalizedStringTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class CashPaymentAdminForm extends AbstractType
{
    private ProfileToStringTransformer $profileTransformer;

    public function __construct(ProfileToStringTransformer $profileTransformer)
    {
        $this->profileTransformer = $profileTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'profile',
                HiddenType::class,
                [
                    'data' => $options['profile'],
                    'data_class' => null
                ]
            )
            ->add('amount', \Symfony\Component\Form\Extension\Core\Type\NumberType::class, [
                'attr' => [
                    'placeholder' => 'EUR'
                ],
                'label' => false,
                'rounding_mode' => NumberToLocalizedStringTransformer::ROUND_DOWN
            ])
            ->add('submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, [
                'attr' => [
                    'class' => 'button small'
                ],
                'label' => 'OK'
            ]);

        $builder->get('profile')->addModelTransformer($this->profileTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => 'App\Mealz\AccountingBundle\Entity\Transaction',
            'profile' => null
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getBlockPrefix(): string
    {
        return 'cash';
    }
}
