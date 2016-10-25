<?php

namespace Mealz\MealBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DishVariationType
 *
 * @package Mealz\MealBundle\Form\Type
 * @author  Chetan Thapliyal <chetan.thapliyal@aoe.com>
 */
class DishVariationForm extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('description_de')
			->add('description_en');
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array('data_class' => 'Mealz\MealBundle\Entity\DishVariation'));
	}
}
