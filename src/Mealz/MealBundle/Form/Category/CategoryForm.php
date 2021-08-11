<?php

namespace App\Mealz\MealBundle\Form\Category;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * form to add or edit a dish
 */
class CategoryForm extends AbstractType
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
            ->add(
                'title_en',
                TextType::class,
                array(
                    'attr' => array(
                        'placeholder' => 'form.placeholder.title',
                    ),
                    'translation_domain' => 'general',
                )
            )
            ->add(
                'title_de',
                TextType::class,
                array(
                    'attr' => array(
                        'placeholder' => 'form.placeholder.title',
                    ),
                    'translation_domain' => 'general',
                )
            )
            ->add(
                'save',
                SubmitType::class,
                array(
                    'label' => 'button.save',
                    'translation_domain' => 'actions',
                    'attr' => [
                        'class' => 'button small',
                    ],
                )
            );
    }

    /**
     * set the Default Options
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'App\Mealz\MealBundle\Entity\Category',
                'intention' => 'category_type',
            )
        );
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getBlockPrefix()
    {
        return 'category';
    }
}
