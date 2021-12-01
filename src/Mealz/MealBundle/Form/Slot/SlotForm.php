<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Form\Slot;

use App\Mealz\MealBundle\Entity\Slot;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form to create and edit meal slots.
 */
class SlotForm extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'form.create-slot.title',
                'attr' => [
                    'placeholder' => 'form.placeholder.title',
                ],
                'translation_domain' => 'general',
            ])
            ->add('limit', IntegerType::class, [
                'label' => 'form.create-slot.limit',
                'attr' => [
                    'placeholder' => 'form.placeholder.limit',
                ],
                'translation_domain' => 'general',
            ])
            ->add('order', IntegerType::class, [
                'label' => 'form.create-slot.order',
                'required' => false,
                'translation_domain' => 'general',
            ])
            ->add('Save', SubmitType::class, [
                'label' => 'button.save',
                'translation_domain' => 'actions',
                'attr' => ['class' => 'button'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Slot::class,
        ]);
    }
}
