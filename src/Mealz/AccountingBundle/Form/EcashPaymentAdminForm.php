<?php

namespace App\Mealz\AccountingBundle\Form;

use App\Mealz\MealBundle\Form\DataTransformer\ProfileToStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EcashPaymentAdminForm extends AbstractType
{
    private ProfileToStringTransformer $profileTransformer;

    public function __construct(
        ProfileToStringTransformer $profileTransformer)
    {
        $this->profileTransformer = $profileTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /*
         * if more payment methods are available remove 'data' => 0 from pay method
         */
        $builder
            ->add('profile', HiddenType::class, [
                'data' => $options['profile'],
                'data_class' => null,
            ])
            ->add('orderid', HiddenType::class, [
                'data_class' => null,
            ])
            ->add('amount', MoneyType::class, [
                'label' => 'payment.transaction_history.amount',
                'data' => $options['balance'],
                'data_class' => null,
            ])
            ->add('paymethod', ChoiceType::class, [
                'choices' => [
                    'payment.transaction_history.paypal',
                ],
                'attr' => [
                    'class' => 'button small',
                ],
                'label' => 'false',
                'expanded' => 'false',
                'data' => 0,
            ]);

        $builder->get('profile')->addModelTransformer($this->profileTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => 'App\Mealz\AccountingBundle\Entity\Transaction',
            'profile' => null,
            'balance' => null,
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getBlockPrefix(): string
    {
        return 'ecash';
    }
}
