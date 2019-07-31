<?php

namespace Mealz\AccountingBundle\Form;

use Doctrine\ORM\EntityManager;
use Mealz\MealBundle\Form\DataTransformer\ProfileToStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\NumberToLocalizedStringTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PaypalPaymentAdminForm extends AbstractType
{

    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $profileTransformer = new ProfileToStringTransformer($this->em);

        $builder
            ->add(
                'profile', HiddenType::class, array(
                'data' => $options['profile'],
                'data_class' => null
            ))
            ->add('amount', 'number', array(
                'attr' => array(
                    'placeholder' => 'EUR'
                ),
                'rounding_mode' => NumberToLocalizedStringTransformer::ROUND_DOWN,
                'label' => 'payment.transaction_history.amount'
            ))
            ->add('ppsubmit', 'submit', array(
                'attr' => array(
                    'class' => 'button small'
                ),
                'label' => 'payment.transaction_history.pay'
            ))
            ->add('paymethod', ChoiceType::class, array(
                'choices' => [
                    'payment.transaction_history.paypal'
                ],
                'attr' => array(
                    'class' => 'button small'
                ),
                'label' => 'false',
                'expanded' => 'false'
            ));

        $builder->get('profile')->addModelTransformer($profileTransformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mealz\AccountingBundle\Entity\Transaction',
            'profile' => null
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'paypal';
    }
}
