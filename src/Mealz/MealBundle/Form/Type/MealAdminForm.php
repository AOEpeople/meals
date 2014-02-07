<?php

namespace Mealz\MealBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * form to add or edit a meal
 */
class MealAdminForm extends AbstractType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('dateTime', 'datetime', array('widget' => 'single_text'))
			// "dish_selector_creator" see DishSelectorCreatorType
			->add('dish', 'dish_selector_creator', array(
				'class' => 'MealzMealBundle:Dish',
				'property' => 'title_en',
			))
			->add('save', 'submit')
		;
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'Mealz\MealBundle\Entity\Meal',
		));
	}

	/**
	 * Returns the name of this type.
	 *
	 * @return string The name of this type
	 */
	public function getName() {
		return 'meal';
	}
}