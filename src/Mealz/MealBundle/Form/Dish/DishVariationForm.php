<?php

namespace App\Mealz\MealBundle\Form\Dish;

use App\Mealz\MealBundle\Entity\DishVariation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class DishVariationType
 *
 * @package Mealz\MealBundle\Form\Type
 * @author  Chetan Thapliyal <chetan.thapliyal@aoe.com>
 */
class DishVariationForm extends AbstractType
{
    /**
     * @var integer $price
     */
    protected $price;

    /**
     * DishVariationForm constructor.
     * @param integer $price
     */
    public function __construct($price)
    {
        $this->price = $price;
    }

    /**
     * build the Form
     * @param FormBuilderInterface $builder
     * @param array $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'title_de',
                TextType::class,
                [
                    'required' => true,
                    'attr' => ['placeholder' => 'form.placeholder.title'],
                    'translation_domain' => 'general',
                ]
            )
            ->add(
                'title_en',
                TextType::class,
                [
                    'required' => true,
                    'attr' => ['placeholder' => 'form.placeholder.title'],
                    'translation_domain' => 'general',
                ]
            )
            ->add(
                'save',
                SubmitType::class,
                [
                    'label' => 'button.save',
                    'translation_domain' => 'actions',
                    'attr' => ['class' => 'button small'],
                ]
            );

        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                /** @var DishVariation $dishvariation */
                $dishvariation = $event->getData();
                $dishvariation->setPrice($this->price);
                $event->setData($dishvariation);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'App\Mealz\MealBundle\Entity\DishVariation',
                'intention' => 'dishvariation_type',
            )
        );
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getBlockPrefix(): string
    {
        return 'dishvariation';
    }
}
