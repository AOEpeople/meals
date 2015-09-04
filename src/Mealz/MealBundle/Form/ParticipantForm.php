<?php


namespace Mealz\MealBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * form to add or edit a participant
 */
class ParticipantForm extends AbstractType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		if($options['allow_guest'] == TRUE) {
			$builder->add('guestName', 'text', array(
				'required' => FALSE
			));
		}
		if($options['allow_cost_absorption']) {
			$builder->add('costAbsorbed', 'checkbox', array(
				'required' => FALSE,
			));
		}
		$builder
			->add('comment', 'textarea', array(
				'required' => FALSE
			))
			->add('save', 'submit')
		;
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'Mealz\MealBundle\Entity\Participant',
			'allow_guest' => FALSE,
			'allow_cost_absorption' => FALSE,
		));
	}

	/**
	 * Returns the name of this type.
	 *
	 * @return string The name of this type
	 */
	public function getName() {
		return 'participant';
	}
}