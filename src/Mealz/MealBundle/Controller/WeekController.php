<?php

namespace Mealz\MealBundle\Controller;

use Doctrine\ORM\UnitOfWork;
use Mealz\MealBundle\Entity\Meal;
use Mealz\MealBundle\Form\MealAdminForm;
use Mealz\MealBundle\Form\WeekForm;
use Mealz\MealBundle\Service\Workday;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\VarDumper\VarDumper;

class WeekController extends BaseController
{

    public function listAction()
    {
        $meals = $this->getMealRepository()->getSortedMeals(
            new \DateTime('-2 hours'),
            null,
            null,
            array(
                'load_dish' => true,
                'load_participants' => true,
            )
        );

        return $this->render('MealzMealBundle:MealAdmin:list.html.twig', array(
            'meals' => $meals
        ));
    }

    public function newAction()
    {
        /*
         * @TODO:
         * get current week
         * set choices current -1 to +4 weeks
         * render form
         */

        $startTime = new \DateTime('monday next week');

        $form = $this->createForm(new WeekForm(), null, array(
            'startTime' => $startTime,
        ));


        return $this->render('MealzMealBundle:MealAdmin:createWeek.html.twig', array(
            'form' => $form->createView()
        ));
    }
}