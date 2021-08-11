<?php

namespace App\Mealz\MealBundle\Form\Guest;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileForm extends AbstractType
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
                ->add('name', TextType::class, array(
                    'required' => false,
                    'attr' => array(
                        'placeholder' => 'form.placeholder.name'
                    ),
                    'translation_domain' => 'general'
                ))
                ->add('firstName', TextType::class, array(
                    'required' => false,
                    'attr' => array(
                        'placeholder' => 'form.placeholder.first_name'
                    ),
                    'translation_domain' => 'general'
                ))
                ->add('company', TextType::class, array(
                    'required' => false,
                    'attr' => array(
                        'placeholder' => 'form.placeholder.company'
                    ),
                    'translation_domain' => 'general'
                ))
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Mealz\UserBundle\Entity\Profile',
        ));
    }
}
