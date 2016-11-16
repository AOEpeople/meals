<?php

namespace Mealz\MealBundle\Form\Guest;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('name', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'form.placeholder.name'
                    ),
                    'translation_domain' => 'general'
                ))
                ->add('firstName', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'form.placeholder.first_name'
                    ),
                    'translation_domain' => 'general'
                ))
                ->add('company', TextType::class, array(
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
            'data_class' => 'Mealz\UserBundle\Entity\Profile',
        ));
    }

}