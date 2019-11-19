<?php


namespace Mealz\MealBundle\Twig\Extension;

use Mealz\MealBundle\Entity\Meal;
use Mealz\MealBundle\Service\Link as LinkService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\TwigFunction;

class Link extends \Twig_Extension {

	/**
	 * @var LinkService
	 */
	protected $linkService;

	public function __construct(LinkService $linkService) {
		$this->linkService = $linkService;
	}

	public function getFunctions() {
		return array(
			new TwigFunction('link', [$this, 'link']),
		);
	}

	public function link($object, $action = NULL, $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH) {
		return $this->linkService->link($object, $action, $referenceType);
	}

	/**
	 * Returns the name of the extension.
	 *
	 * @return string The extension name
	 */
	public function getName() {
		return 'link';
	}
}