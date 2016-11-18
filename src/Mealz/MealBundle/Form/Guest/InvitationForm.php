<?php

namespace Mealz\MealBundle\Form\Guest;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * form to show invitation for guest
 */
class InvitationForm extends AbstractType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		// passing by Day value to the DayForm in order to render only particular day
		$builder
			->add('day', DayForm::class, array(
					'data' => $options['data']->getDay()
			))
			->add('profile', ProfileForm::class)
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
			'data_class' => 'Mealz\MealBundle\Entity\InvitationWrapper',
            'csrf_protection' => false
		));
	}

	/**
	 * Returns the name of this type.
	 *
	 * @return string The name of this type
	 */
	public function getName() {
		return 'invitation_form';
	}
}