<?php


namespace Mealz\MealBundle\Controller;


use Doctrine\ORM\Query;
use Mealz\MealBundle\Entity\Meal;
use Mealz\MealBundle\Entity\Participant;
use Mealz\MealBundle\Entity\ParticipantRepository;
use Mealz\UserBundle\Entity\Zombie;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MealController extends BaseController {

	/**
	 * @return ParticipantRepository
	 */
	protected function getParticipantRepository() {
		return $this->getDoctrine()->getRepository('MealzMealBundle:Participant');
	}

	public function indexAction() {
		/** @var Query $query */
		$query = $this->getDoctrine()->getManager()->createQuery('
			SELECT m,d
			FROM MealzMealBundle:Meal m
			JOIN m.dish d
			WHERE m.dateTime > :min_date
			ORDER BY m.dateTime ASC
		');
		$query->setParameter('min_date', new \DateTime());
		$query->setMaxResults(4);
		$meals = $query->execute();

		return $this->render('MealzMealBundle:Meal:index.html.twig', array(
			'meals' => $meals
		));
	}

	public function listAction() {
		/** @var Query $query */
		$query = $this->getDoctrine()->getManager()->createQuery('
			SELECT m,d,p,u
			FROM MealzMealBundle:Meal m
			JOIN m.dish d
			LEFT JOIN m.participants p
			LEFT JOIN p.user u
			WHERE m.dateTime > :min_date
			ORDER BY m.dateTime ASC
		');
		$query->setParameter('min_date', new \DateTime('-2 hours'));
		$meals = $query->execute();

		return $this->render('MealzMealBundle:Meal:list.html.twig', array(
			'meals' => $meals
		));
	}

	public function showAction($meal) {
		/** @var Query $query */
		$query = $this->getDoctrine()->getManager()->createQuery('
			SELECT m,d,p,u
			FROM MealzMealBundle:Meal m
			JOIN m.dish d
			LEFT JOIN m.participants p
			LEFT JOIN p.user u
			WHERE m.id = :meal_id
		');
		$meal = $query->execute(array('meal_id' => intval($meal)));
		if(!$meal) {
			throw $this->createNotFoundException('The given meal does not exist');
		} else {
			$meal = current($meal);
		}

		return $this->render('MealzMealBundle:Meal:show.html.twig', array(
			'meal' => $meal
		));
	}

	/**
	 * let the currently logged in user join the given meal
	 *
	 * @param Meal $meal
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
	 */
	public function joinAction(Meal $meal) {
		if(!$this->getUser() instanceof Zombie) {
			throw new AccessDeniedException();
		}
		if(!$this->getDoorman()->isUserAllowedToJoin($meal)) {
			throw new AccessDeniedException('You are not allowed to join this meal.');
		}

		try {
			$participant = new Participant();
			$participant->setUser($this->getUser());
			$participant->setMeal($meal);

			// that method ensures consistency by using a transaction
			$this->getParticipantRepository()->addParticipant($participant);

			$this->get('session')->getFlashBag()->add(
				'success',
				'You joined as participant to the meal.'
			);
		} catch (\InvalidArgumentException $e) {
			$this->addFlashMessage('You are already joining this meal.', 'info');
		}

		return $this->redirect($this->generateUrlTo($meal));
	}

}