<?php

namespace Mealz\MealBundle\Form\Dish;

use Mealz\MealBundle\Entity\Category;
use Mealz\MealBundle\Entity\Dish;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * form to add or edit a dish
 */
class DishForm extends AbstractType {

	/**
	 * @var integer $price
	 */
	protected $price;

	/**
	 * DishForm constructor.
	 * @param integer $price
	 */
	public function __construct($price)
	{
		$this->price = $price;
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('title_en', TextType::class, array(
				'attr' => array(
					'placeholder' => 'form.placeholder.title'
				),
				'translation_domain' => 'general'
			))
			->add('title_de', TextType::class, array(
				'attr' => array(
					'placeholder' => 'form.placeholder.title'
				),
				'translation_domain' => 'general'
			))
			->add('description_en', TextType::class, array(
				'required' => FALSE,
				'attr' => array(
					'placeholder' => 'form.placeholder.description'
				),
				'translation_domain' => 'general'
			))
			->add('description_de', TextType::class, array(
				'required' => FALSE,
				'attr' => array(
					'placeholder' => 'form.placeholder.description'
				),
				'translation_domain' => 'general'
			))
			->add('category', EntityType::class, array(
				'class' => 'MealzMealBundle:Category',
				'required' => false,
                'choice_label' => function ($category) {
                    /** @var Category $category */
                    return $category->getTitle();
                },
				'placeholder' => 'form.placeholder.category',
				'translation_domain' => 'general',
			))
			->add('save', SubmitType::class, array(
				'label' => 'button.save',
				'translation_domain' => 'actions',
                'attr' => [
                    'class' => 'button small'
                ]
			))
		;

		$builder->addEventListener(FormEvents::SUBMIT, function(FormEvent $event){
			/** @var Dish $dish */
			$dish = $event->getData();
			$dish->setPrice($this->price);
			$event->setData($dish);
		});
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