<?php

namespace Mealz\MealBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * a custom type for a meal entity.
 *
 * It renders as an input field with a datalist (aka autocomplete) and if
 * a dish with the typed in name does not exist it is created dynamically.
 */
class HiddenDishType extends AbstractType {
    /**
     * @var DataTransformerInterface $transformer
     */
    private $transformer;

    /**
     * Constructor
     *
     * @param DataTransformerInterface $transformer
     */
    public function __construct(DataTransformerInterface $transformer)
    {
        $this->transformer = $transformer;
    }

	public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->addModelTransformer($this->transformer);
	}

	public function getParent() {
		return 'hidden';
	}

	public function getName() {
		return 'hidden_entity';
	}

}