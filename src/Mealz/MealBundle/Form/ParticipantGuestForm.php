<?php


namespace Mealz\MealBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * form to add or edit a participant
 */
class ParticipantGuestForm extends ParticipantForm {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('guestName', 'text', array(
			'required' => TRUE,
			'constraints' => array(
				new NotBlank(), // user should not be allowed to remove the name
			),
		));
		if ($options['allow_cost_absorption']) {
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