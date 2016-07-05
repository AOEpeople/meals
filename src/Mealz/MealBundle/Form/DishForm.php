<?php

namespace Mealz\MealBundle\Form;

use Mealz\MealBundle\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * form to add or edit a dish
 */
class DishForm extends AbstractType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('title_en', TextType::class, array(
				'attr' => array(
					'placeholder' => 'title'
				),
				'translation_domain' => 'general'
			))
			->add('title_de', TextType::class, array(
				'attr' => array(
					'placeholder' => 'title'
				),
				'translation_domain' => 'general'
			))
			->add('description_en', TextType::class, array(
				'required' => FALSE,
				'attr' => array(
					'placeholder' => 'description'
				),
				'translation_domain' => 'general'
			))
			->add('description_de', TextType::class, array(
				'required' => FALSE,
				'attr' => array(
					'placeholder' => 'description'
				),
				'translation_domain' => 'general'
			))
			->add('category', EntityType::class, array(
				'class' => 'MealzMealBundle:Category',
				'required' => false,
                'choice_label' => function ($category) {
                    /** @var Category $category */
                    return $category->getTitle();
                }
			))
			->add('save', SubmitType::class, array(
				'label' => 'Save',
                'attr' => [
                    'class' => 'button small'
                ]
			))
		;
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'Mealz\MealBundle\Entity\Dish',
			'intention' => 'dish_type'
		));
	}

	/**
	 * Returns the name of this type.
	 *
	 * @return string The name of this type
	 */
	public function getName() {
		return 'dish';
	}
}