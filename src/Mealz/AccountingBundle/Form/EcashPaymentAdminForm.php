<?php

namespace Mealz\AccountingBundle\Form;

use Doctrine\ORM\EntityManager;
use Mealz\AccountingBundle\Controller\AccountingAdminController;
use Mealz\AccountingBundle\Controller\Payment\EcashController;
use Mealz\AccountingBundle\Service\Wallet;
use Mealz\MealBundle\Form\DataTransformer\ProfileToStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\NumberToLocalizedStringTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class EcashPaymentAdminForm extends AbstractType
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

        /**
        * if more Paymentmethodsare available remove 'data' => 0 from paymehtod
        */
        $builder
            ->add('profile', HiddenType::class, array(
                'data' => $options['profile'],
                'data_class' => null
            ))
            ->add('orderid', HiddenType::class, array(
                'data_class' => null
            ))
            ->add('amount', MoneyType::class, array(
                'label' => 'payment.transaction_history.amount',
                'data' => $options['balance'],
                'pattern' => '\d*([.,]?\d+)',
                'data_class' => null
            ))
            ->add('paymethod', ChoiceType::class, array(
                'choices' => array(
                    'payment.transaction_history.paypal'
                ),
                'attr' => array(
                    'class' => 'button small'
                ),
                'label' => 'false',
                'expanded' => 'false',
                'data' => 0
            ));

        $builder->get('profile')->addModelTransformer($profileTransformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mealz\AccountingBundle\Entity\Transaction',
            'profile' => null,
            'balance' => null,
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'ecash';
    }
}
