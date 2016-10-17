<?php

namespace Mealz\MealBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * form to add or edit a dish
 */
class CategoryForm extends AbstractType {

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
			->add('save', SubmitType::class, array(
				'label' => 'button.save',
				'translation_domain' => 'actions',
                'attr' => [
                    'class' => 'button small'
                ]
			))
		;
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'Mealz\MealBundle\Entity\Category',
			'intention' => 'category_type'
		));
	}

	/**
	 * Returns the name of this type.
	 *
	 * @return string The name of this type
	 */
	public function getName() {
		return 'category';
	}
}