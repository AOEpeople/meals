<?php

namespace App\Mealz\MealBundle\Form\Category;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryForm extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'title_en',
                TextType::class,
                [
                    'attr' => [
                        'placeholder' => 'form.placeholder.title',
                    ],
                    'translation_domain' => 'general',
                ]
            )
            ->add(
                'title_de',
                TextType::class,
                [
                    'attr' => [
                        'placeholder' => 'form.placeholder.title',
                    ],
                    'translation_domain' => 'general',
                ]
            )
            ->add(
                'save',
                SubmitType::class,
                [
                    'label' => 'button.save',
                    'translation_domain' => 'actions',
                    'attr' => [
                        'class' => 'button small',
                    ],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => 'App\Mealz\MealBundle\Entity\Category',
                'intention' => 'category_type',
            ]
        );
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getBlockPrefix(): string
    {
        return 'category';
    }
}
