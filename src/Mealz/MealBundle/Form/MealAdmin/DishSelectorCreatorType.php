<?php

namespace Mealz\MealBundle\Form\Type;

use Mealz\MealBundle\Form\DataTransformer\DishStringToValuesTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * a custom type for a meal entity.
 *
 * It renders as an input field with a datalist (aka autocomplete) and if
 * a dish with the typed in name does not exist it is created dynamically.
 */
class DishSelectorCreatorType extends AbstractType
{
    /**
     * build the form
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->resetViewTransformers();
        $builder->addViewTransformer(
            new DishStringToValuesTransformer(
                $options['choice_list'],
                $options['em'],
                $options['class'],
                $options['property']
            )
        );
    }

    public function getParent()
    {
        return 'entity';
    }

    public function getName()
    {
        return 'dish_selector_creator';
    }
}
