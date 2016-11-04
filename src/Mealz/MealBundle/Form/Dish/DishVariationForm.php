<?php

namespace Mealz\MealBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
			->add('description_de', TextType::class, [
				'required' => TRUE,
				'attr' => ['placeholder' => 'form.placeholder.description'],
				'translation_domain' => 'general'
			])
			->add('description_en', TextType::class, [
				'required' => TRUE,
				'attr' => ['placeholder' => 'form.placeholder.description'],
				'translation_domain' => 'general'
			])
			->add('save', SubmitType::class, [
				'label' => 'button.save',
				'translation_domain' => 'actions',
				'attr' => ['class' => 'button small']
			]);
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array('data_class' => 'Mealz\MealBundle\Entity\DishVariation'));
	}
}
