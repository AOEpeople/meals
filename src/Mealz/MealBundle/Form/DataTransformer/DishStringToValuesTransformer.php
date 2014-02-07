<?php

namespace Mealz\MealBundle\Form\DataTransformer;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\EntityManager;
use Mealz\MealBundle\Entity\Dish;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\Extension\Core\DataTransformer\ChoiceToValueTransformer;

/**
 * a helper class for the DishSelectorCreatorType.
 *
 * The form gets the title of a dish as argument (string). This transformer tries
 * to find that string in the choices list or, if it does not find it, creates a new
 * Dish entity on the fly.
 */
class DishStringToValuesTransformer extends ChoiceToValueTransformer {

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;
	protected $className;
	protected $propertyName;

	/**
	 * Constructor.
	 *
	 * @param ChoiceListInterface $choiceList
	 * @param \Doctrine\ORM\EntityManager $em
	 * @param $className
	 * @param $propertyName
	 */
	public function __construct(ChoiceListInterface $choiceList, EntityManager $em, $className, $propertyName)
	{
		$this->em = $em;
		$this->className = $className;
		$this->propertyName = $propertyName;

		parent::__construct($choiceList);
	}

	/**
	 * resolve a dish or string into a string that can be used as value in an input field
	 *
	 * @param mixed $choice
	 * @return string
	 * @throws \RuntimeException
	 */
	public function transform($choice) {
		if($choice instanceof Dish) {
			$getterName = 'get' . Inflector::classify($this->propertyName);
			if(!method_exists($choice, $getterName)) {
				throw new \RuntimeException(sprintf(
					'Cannot get property %s of Dish, because %s() is not defined.',
					$this->propertyName, $getterName
				));
			}

			return $choice->$getterName();
		} else {
			return parent::transform($choice);
		}
	}


	/**
	 * resolve a string into a Dish (by title) or create a new one
	 */
	public function reverseTransform($value)
	{
		if (null !== $value && !is_scalar($value)) {
			throw new TransformationFailedException('Expected a scalar.');
		}

		// These are now valid ChoiceList values, so we can return null
		// right away
		if ('' === $value || null === $value) {
			return null;
		}

		$dishRepository = $this->em->getRepository($this->className);
		$dishes = $dishRepository->findBy(array($this->propertyName => $value));

		if(1 < count($dishes)) {
			throw new TransformationFailedException(sprintf('The choice "%s" is not unique', $value));
		} elseif(0 === count($dishes)) {
			$dish = new Dish();
			$setterName = 'set' . Inflector::classify($this->propertyName);
			if(!method_exists($dish, $setterName)) {
				throw new \RuntimeException(sprintf(
					'Cannot set property %s of Dish, because %s() is not defined.',
					$this->propertyName, $setterName
				));
			}

			$dish->$setterName($value);
		} else {
			$dish = current($dishes);
		}

		return $dish;
	}
}