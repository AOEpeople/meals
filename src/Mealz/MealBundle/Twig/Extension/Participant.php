<?php


namespace Mealz\MealBundle\Twig\Extension;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Mealz\MealBundle\Entity\Meal;
use Mealz\MealBundle\Entity\ParticipantRepository;
use Mealz\UserBundle\Entity\Zombie;
use Symfony\Component\Security\Core\SecurityContext;

class Participant extends \Twig_Extension {

	/**
	 * @var EntityManager
	 */
	protected $entityManager;

	/**
	 * @var SecurityContext
	 */
	protected $securityContext;

	public function __construct(EntityManager $entityManager, SecurityContext $securityContext) {
		$this->entityManager = $entityManager;
		$this->securityContext = $securityContext;
	}

	public function getFunctions() {
		return array(
			'is_participant' => new \Twig_Function_Method($this, 'isParticipant'),
		);
	}

	/**
	 * return TRUE if the given user has booked the meal
	 *
	 * @param Meal $meal
	 * @return bool
	 */
	public function isParticipant(Meal $meal)  {
		$user = $this->securityContext->getToken()->getUser();
		if(!$user instanceof Zombie) {
			return FALSE;
		}

		/** @var ParticipantRepository $participantRepository */
		$participantRepository = $this->entityManager->getRepository('MealzMealBundle:Participant');
		return $participantRepository->isParticipant($user, $meal);

	}

	/**
	 * Returns the name of the extension.
	 *
	 * @return string The extension name
	 */
	public function getName() {
		return 'participant';
	}
}