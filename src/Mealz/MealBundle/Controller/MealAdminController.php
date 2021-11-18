<?php

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Dish;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\DishRepository;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Entity\WeekRepository;
use App\Mealz\MealBundle\Form\MealAdmin\WeekForm;
use App\Mealz\MealBundle\Validator\Constraints\DishConstraint;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;

class MealAdminController extends BaseController
{
    /**
     * @Security("is_granted('ROLE_KITCHEN_STAFF')")
     */
    public function list(WeekRepository $weekRepository): Response
    {
        $weeks = [];
        $dateTime = new DateTime();

        for ($i = 0; $i < 8; $i++) {
            $modifiedDateTime = clone($dateTime);
            $modifiedDateTime->modify('+'.$i.' weeks');
            $week = $weekRepository->findOneBy(
                [
                    'year' => $modifiedDateTime->format('o'),
                    'calendarWeek' => $modifiedDateTime->format('W'),
                ]
            );

            if (null === $week) {
                $week = new Week();
                $week->setYear($modifiedDateTime->format('o'));
                $week->setCalendarWeek($modifiedDateTime->format('W'));
            }

            $weeks[] = $week;
        }

        return $this->render('MealzMealBundle:MealAdmin:list.html.twig', ['weeks' => $weeks]);
    }

    /**
     * New action
     *
     * @return RedirectResponse|Response
     *
     * @throws ORMException
     * @throws OptimisticLockException
     *
     * @Security("is_granted('ROLE_KITCHEN_STAFF')")
     */
    public function new(Request $request, DateTime $date, WeekRepository $weekRepository)
    {
        $week = $weekRepository->findOneBy([
            'year' => $date->format('o'),
            'calendarWeek' => $date->format('W'),
        ]);

        if (null !== $week) {
            return $this->redirectToRoute('MealzMealBundle_Meal_edit', ['week' => $week->getId()]);
        }

        $week = $this->generateEmptyWeek($date);
        $form = $this->createForm(WeekForm::class, $week);

        // handle form submission
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->get('Cancel')->isClicked()) {
                return $this->redirectToRoute('MealzMealBundle_Meal');
            }

            if ($form->isValid()) {
                /** @var EntityManager $entitiyManager */
                $entitiyManager = $this->getDoctrine()->getManager();
                $entitiyManager->persist($week);
                $entitiyManager->flush();

                $message = $this->get('translator')->trans('week.created', [], 'messages');
                $this->addFlashMessage($message, 'success');

                return $this->redirect(
                    $this->generateUrl(
                        'MealzMealBundle_Meal_edit',
                        ['week' => $week->getId()]
                    )
                );
            }
        }

        $dishRepository = $this->getDoctrine()->getRepository(Dish::class);
        $dishes = $dishRepository->getSortedDishesQueryBuilder()->getQuery()->getResult();

        return $this->render(
            'MealzMealBundle:MealAdmin:week.html.twig',
            [
                'week' => $week,
                'dishes' => $dishes,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Edit action
     *
     * @return RedirectResponse|Response
     *
     * @throws ORMException
     * @throws OptimisticLockException
     *
     * @Security("is_granted('ROLE_KITCHEN_STAFF')")
     */
    public function edit(Request $request, Week $week, DishRepository $dishRepository)
    {
        $dishes = $dishRepository->getSortedDishesQueryBuilder()->getQuery()->getResult();
        $form = $this->createForm(WeekForm::class, $week);

        // handle form submission
        if ($request->isMethod('POST') === true) {
            $form->handleRequest($request);

            if ($form->get('Cancel')->isClicked()) {
                return $this->redirectToRoute('MealzMealBundle_Meal');
            }

            if ($form->isValid() === true) {
                /** @var EntityManager $entitiyManager */
                $entitiyManager = $this->getDoctrine()->getManager();
                $entitiyManager->persist($week);
                $entitiyManager->flush();

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
                ['week' => $week->getId()]
            );
        }

        return $this->render(
            'MealzMealBundle:MealAdmin:week.html.twig',
            [
                'dishes' => $dishes,
                'week' => $week,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Generate empty week action
     */
    protected function generateEmptyWeek(DateTime $dateTime): Week
    {
        $week = new Week();
        $week->setYear($dateTime->format('o'));
        $week->setCalendarWeek($dateTime->format('W'));

        $dateTimeModifier = $this->getParameter('mealz.lock_toggle_participation_at');

        $days = $week->getDays();
        for ($i = 0; $i < 5; $i++) {
            $dayDateTime = clone($week->getStartTime());
            $dayDateTime->modify('+'.$i.' days');
            $dayDateTime->setTime(12, 00);
            $lockParticipationDT = clone($dayDateTime);
            $lockParticipationDT->modify($dateTimeModifier);

            $day = new Day();
            $day->setDateTime($dayDateTime);
            $day->setLockParticipationDateTime($lockParticipationDT);
            $day->setWeek($week);
            $days->add($day);
        }

        return $week;
    }
}
