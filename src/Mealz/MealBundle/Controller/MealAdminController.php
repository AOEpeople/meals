<?php

namespace Mealz\MealBundle\Controller;

use Doctrine\ORM\EntityManager;
use Mealz\MealBundle\Entity\Day;
use Mealz\MealBundle\Entity\Week;
use Mealz\MealBundle\Entity\WeekRepository;
use Mealz\MealBundle\Form\MealAdmin\WeekForm;
use Mealz\MealBundle\Validator\Constraints\DishConstraint;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\VarDumper\VarDumper;

class MealAdminController extends BaseController
{

    /**
     * List action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted('ROLE_KITCHEN_STAFF');

        /** @var WeekRepository $weekRepository */
        $weekRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Week');

        $weeks = array();

        $dateTime = new \DateTime();

        for ($i = 0; $i < 8; $i++) {
            $modifiedDateTime = clone($dateTime);
            $modifiedDateTime->modify('+'.$i.' weeks');
            $week = $weekRepository->findOneBy(
                array(
                    'year' => $modifiedDateTime->format('Y'),
                    'calendarWeek' => $modifiedDateTime->format('W'),
                )
            );

            if (null === $week) {
                $week = new Week();
                $week->setYear($modifiedDateTime->format('Y'));
                $week->setCalendarWeek($modifiedDateTime->format('W'));
            }

            array_push($weeks, $week);
        }

        return $this->render(
            'MealzMealBundle:MealAdmin:list.html.twig',
            array('weeks' => $weeks)
        );
    }

    /**
     * New action
     *
     * @param Request $request request
     * @param \DateTime $date on date
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request, \DateTime $date)
    {
        $this->denyAccessUnlessGranted('ROLE_KITCHEN_STAFF');

        $qbDishes = $this->get('mealz_meal.repository.dish');
        $dishes = $qbDishes->getSortedDishesQueryBuilder()->getQuery()->getResult();

        /** @var WeekRepository $weekRepository */
        $weekRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Week');
        $week = $weekRepository->findOneBy(
            array(
                'year' => $date->format('Y'),
                'calendarWeek' => $date->format('W'),
            )
        );

        if (null !== $week) {
            return $this->redirectToRoute(
                'MealzMealBundle_Meal_edit',
                array('week' => $week->getId())
            );
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

                return $this->redirect(
                    $this->generateUrl(
                        'MealzMealBundle_Meal_edit',
                        array('week' => $week->getId())
                    )
                );
            }
        }

        return $this->render(
            'MealzMealBundle:MealAdmin:week.html.twig',
            array(
                'week' => $week,
                'dishes' => $dishes,
                'form' => $form->createView(),
            )
        );
    }

    /**
     * Edit action
     *
     * @param Request $request request
     * @param Week $week for the week
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, Week $week)
    {
        $this->denyAccessUnlessGranted('ROLE_KITCHEN_STAFF');

        $qbDishes = $this->get('mealz_meal.repository.dish');
        $dishes = $qbDishes->getSortedDishesQueryBuilder()->getQuery()->getResult();

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
                    if ($error->getCause() instanceof ConstraintViolation
                        && $error->getCause()->getConstraint() instanceof DishConstraint
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

            return $this->redirectToRoute(
                'MealzMealBundle_Meal_edit',
                array('week' => $week->getId())
            );
        }

        //VarDumper::dump($form->createView());die();
        return $this->render(
            'MealzMealBundle:MealAdmin:week.html.twig',
            array(
                'dishes' => $dishes,
                'week' => $week,
                'form' => $form->createView(),
            )
        );
    }

    /**
     * Generate empty week action
     *
     * @param \DateTime $dateTime on date
     *
     * @return Week
     */
    protected function generateEmptyWeek(\DateTime $dateTime)
    {
        $week = new Week();
        $week->setYear($dateTime->format('Y'));
        $week->setCalendarWeek($dateTime->format('W'));

        $dateTimeModifier = $this->getParameter('mealz.lock_toggle_participation_at');

        $days = $week->getDays();
        for ($i = 0; $i < 5; $i++) {
            $dayDateTime = clone($week->getStartTime());
            $dayDateTime->modify('+'.$i.' days');
            $dayDateTime->setTime(12, 00);
            $lockParticipationDateTime = clone($dayDateTime);
            $lockParticipationDateTime->modify($dateTimeModifier);

            $day = new Day();
            $day->setDateTime($dayDateTime);
            $day->setLockParticipationDateTime($lockParticipationDateTime);
            $day->setWeek($week);
            $days->add($day);
        }

        return $week;
    }
}
