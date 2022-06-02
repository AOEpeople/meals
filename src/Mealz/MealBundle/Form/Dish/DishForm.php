<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Form\Dish;

use App\Mealz\MealBundle\Entity\Category;
use App\Mealz\MealBundle\Entity\Dish;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DishForm extends AbstractType
{
    protected float $price;

    public function __construct(float $price)
    {
        $this->price = $price;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title_en', TextType::class, [
                'attr' => [
                    'placeholder' => 'form.placeholder.title',
                ],
                'translation_domain' => 'general',
            ])
            ->add('title_de', TextType::class, [
                'attr' => [
                    'placeholder' => 'form.placeholder.title',
                ],
                'translation_domain' => 'general',
            ])
            ->add('description_en', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'form.placeholder.description',
                ],
                'translation_domain' => 'general',
            ])
            ->add('description_de', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'form.placeholder.description',
                ],
                'translation_domain' => 'general',
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'label' => 'Category',
                'required' => false,
                'choice_label' => static function (Category $category) {
                    return $category->getTitle();
                },
                'placeholder' => 'form.placeholder.category',
                'translation_domain' => 'general',
            ])
            ->add('oneServingSize', CheckboxType::class, [
                'required' => false,
                'attr' => ['class' => 'js-switch'],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'button.save',
                'translation_domain' => 'actions',
                'attr' => ['class' => 'button small'],
            ]);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var Dish $dish */
            $dish = $event->getData();
            $dish->setPrice($this->price);
            $event->setData($dish);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Dish::class,
            'intention' => 'dish_type',
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getBlockPrefix(): string
    {
        return 'dish';
    }
}
