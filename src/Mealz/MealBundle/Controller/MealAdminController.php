<?php

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\DishRepository;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Entity\WeekRepository;
use App\Mealz\MealBundle\Event\WeekChangedEvent;
use App\Mealz\MealBundle\Form\MealAdmin\WeekForm;
use App\Mealz\MealBundle\Service\WeekService;
use App\Mealz\MealBundle\Validator\Constraints\DishConstraint;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;

class MealAdminController extends BaseController
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @Security("is_granted('ROLE_KITCHEN_STAFF')")
     */
    public function list(WeekRepository $weekRepository): Response
    {
        $weeks = [];
        $dateTime = new DateTime();

        for ($i = 0; $i < 8; ++$i) {
            $modifiedDateTime = clone $dateTime;
            $modifiedDateTime->modify('+' . $i . ' weeks');
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
     * @return RedirectResponse|Response
     *
     * @throws OptimisticLockException|ORMException
     *
     * @Security("is_granted('ROLE_KITCHEN_STAFF')")
     */
    public function new(Request $request, DateTime $date, WeekRepository $weekRepository, DishRepository $dishRepository)
    {
        $week = $weekRepository->findOneBy([
            'year' => $date->format('o'),
            'calendarWeek' => $date->format('W'),
        ]);

        if (null !== $week) {
            return $this->redirectToRoute('MealzMealBundle_Meal_edit', ['week' => $week->getId()]);
        }

        $dateTimeModifier = $this->getParameter('mealz.lock_toggle_participation_at');
        $week = WeekService::generateEmptyWeek($date, $dateTimeModifier);
        $form = $this->createForm(WeekForm::class, $week);

        // handle form submission
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->get('Cancel')->isClicked()) {
                return $this->redirectToRoute('MealzMealBundle_Meal');
            }

            if ($form->isValid()) {
                $this->updateWeek($week);

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
     * @return RedirectResponse|Response
     *
     * @throws OptimisticLockException|ORMException
     *
     * @Security("is_granted('ROLE_KITCHEN_STAFF')")
     */
    public function edit(Request $request, Week $week, DishRepository $dishRepository)
    {
        $dishes = $dishRepository->getSortedDishesQueryBuilder()->getQuery()->getResult();
        $form = $this->createForm(WeekForm::class, $week);

        // handle form submission
        if (true === $request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('Cancel')->isClicked()) {
                return $this->redirectToRoute('MealzMealBundle_Meal');
            }

            if (true === $form->isValid()) {
                $this->updateWeek($week);

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
     * @throws OptimisticLockException|ORMException
     */
    private function updateWeek(Week $week): void
    {
        /** @var Day $day */
        foreach ($week->getDays() as $day) {
            /** @var Meal $meal */
            foreach ($day->getMeals() as $meal) {
                if (null === $meal->getDish()) {
                    $day->removeMeal($meal);
                }
            }
        }
        /** @var EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($week);
        $entityManager->flush();

        $this->eventDispatcher->dispatch(new WeekChangedEvent($week));
    }
}
