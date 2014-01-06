<?php


namespace Xopn\MealzForZombies\MealzBundle\Controller;


use Doctrine\ORM\Query;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Xopn\MealzForZombies\MealzBundle\Entity\Meal;

class MealController extends Controller {

    public function indexAction() {
        /** @var Query $query */
        $query = $this->getDoctrine()->getManager()->createQuery('
            SELECT m,d
            FROM XopnMealzForZombiesMealzBundle:Meal m
            JOIN m.dish d
            WHERE m.dateTime > :min_date
            ORDER BY m.dateTime ASC
        ');
        $query->setParameter('min_date', new \DateTime());
        $query->setMaxResults(4);
        $meals = $query->execute();

        return $this->render('XopnMealzForZombiesMealzBundle:Meal:index.html.twig', array(
            'meals' => $meals
        ));
    }

    public function listAction() {
        /** @var Query $query */
        $query = $this->getDoctrine()->getManager()->createQuery('
            SELECT m,d
            FROM XopnMealzForZombiesMealzBundle:Meal m
            JOIN m.dish d
            WHERE m.dateTime > :min_date
            ORDER BY m.dateTime ASC
        ');
        $query->setParameter('min_date', new \DateTime('-2 hours'));
        $meals = $query->execute();

        return $this->render('XopnMealzForZombiesMealzBundle:Meal:list.html.twig', array(
            'meals' => $meals
        ));
    }

    public function showAction(Meal $meal) {
        return $this->render('XopnMealzForZombiesMealzBundle:Meal:show.html.twig', array(
            'meal' => $meal
        ));
    }

}