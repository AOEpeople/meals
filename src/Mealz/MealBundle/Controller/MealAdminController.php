<?php

namespace Mealz\MealBundle\Controller;

use Doctrine\ORM\EntityManager;
use Mealz\MealBundle\Entity\Day;
use Mealz\MealBundle\Entity\Meal;
use Mealz\MealBundle\Entity\Week;
use Mealz\MealBundle\Entity\WeekRepository;
use Mealz\MealBundle\Form\WeekForm;
use Mealz\MealBundle\Validator\Constraints\DishConstraint;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\ConstraintViolation;

class MealAdminController extends BaseController {

    public function listAction()
    {
        if (!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

        /** @var WeekRepository $weekRepository */
        $weekRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Week');

        $weeks = array();

        $dateTime = new \DateTime();

        for ($i = 0; $i < 8; $i++) {
            $modifiedDateTime = clone($dateTime);
            $modifiedDateTime->modify('+' . $i . ' weeks');
            $week = $weekRepository->findOneBy(array(
                'year' => $modifiedDateTime->format('Y'),
                'calendarWeek' => $modifiedDateTime->format('W')
            ));

            if (null === $week) {
                $week = new Week();
                $week->setYear($modifiedDateTime->format('Y'));
                $week->setCalendarWeek($modifiedDateTime->format('W'));
            }

            array_push($weeks, $week);
        }

        return $this->render('MealzMealBundle:MealAdmin:list.html.twig', array(
            'weeks' => $weeks
        ));
    }

    public function newAction(Request $request,\DateTime $date)
    {
        if (!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

        /** @var WeekRepository $weekRepository */
        $weekRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Week');
        $week = $weekRepository->findOneBy(array(
            'year' => $date->format('Y'),
            'calendarWeek' => $date->format('W')
        ));

        if (null !== $week) {
            return $this->redirectToRoute('MealzMealBundle_Meal_edit', array(
                'week' => $week->getId()
            ));
        }

        $week = $this->generateEmptyWeek($date);

        $form = $this->createForm(new WeekForm(), $week);

        // handle form submission
        if ($request->isMethod('POST')) {

            $form->handleRequest($request);
            if ($form->get('Cancel')->isClicked()) {
                return $this->redirectToRoute('MealzMealBundle_Meal');
            }

            if ($form->isValid()) {
                /** @var EntityManager $em */
                $em = $this->getDoctrine()->getManager();
                $em->persist($week);
                $em->flush();

                $message = $this->get('translator')->trans('week.created', [], 'messages');
                $this->addFlashMessage($message, 'success');

                return $this->redirect($this->generateUrl('MealzMealBundle_Meal_edit', array(
                    'week' => $week->getId()
                )));
            }
        }

        return $this->render('MealzMealBundle:MealAdmin:week.html.twig', array(
            'form' => $form->createView(),
            'week' => $week
        ));
    }

    public function editAction(Request $request, Week $week)
    {
        if (!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

        foreach($week->getDays() as $day) {
            $this->generateEmptyMealsForDay($day);
        }

        $form = $this->createForm(new WeekForm(), $week);

        // handle form submission
        if ($request->isMethod('POST')) {

            $form->handleRequest($request);

            if ($form->get('Cancel')->isClicked()) {
                return $this->redirectToRoute('MealzMealBundle_Meal');
            }

            if ($form->isValid()) {
                /** @var EntityManager $em */
                $em = $this->getDoctrine()->getManager();
                $em->persist($week);
                $em->flush();

                $message = $this->get('translator')->trans('week.modified', [], 'messages');
                $this->addFlashMessage($message, 'success');
            } else {
                $errors = $form->getErrors(true);
                foreach ($errors as $error) {
                    if ($error->getCause() instanceof ConstraintViolation &&
                        $error->getCause()->getConstraint() instanceof DishConstraint
                    ) {
                        $translator = $this->get('translator');
                        $messageTemplate = $error->getMessageTemplate();
                        $messageParameters = $error->getMessageParameters();
                        $day = $messageParameters['%day%'];
                        $messageParameters['%day%'] = $translator->trans($day, [], 'date');
                        $message = $translator->trans($messageTemplate, $messageParameters, 'messages');
                        $this->addFlashMessage($message, 'danger');
                    }
                }
            }

            return $this->redirectToRoute('MealzMealBundle_Meal_edit', array(
                    'week' => $week->getId()
            ));
        }

        return $this->render('MealzMealBundle:MealAdmin:week.html.twig', array(
            'form' => $form->createView(),
            'week' => $week
        ));
    }

    protected function generateEmptyWeek(\DateTime $dateTime)
    {
        $week = new Week();
        $week->setYear($dateTime->format('Y'));
        $week->setCalendarWeek($dateTime->format('W'));

        $days = $week->getDays();
        for ($i = 0; $i < 5; $i++) {
            $dayDateTime = clone($week->getStartTime());
            $dayDateTime->modify('+' . $i . ' days');
            $day = new Day();
            $day->setDateTime($dayDateTime);
            $this->generateEmptyMealsForDay($day);
            $day->setWeek($week);
            $days->add($day);
        }

        return $week;
    }

    protected function generateEmptyMealsForDay(Day $day)
    {
        while(count($day->getMeals()) < 2) {
            $meal = new Meal();
            $meal->setDay($day);
            $mealDateTime = clone($day->getDateTime());
            $mealDateTime->setTime(12, 00);
            $meal->setDateTime($mealDateTime);
            $day->getMeals()->add($meal);
        }
    }
}