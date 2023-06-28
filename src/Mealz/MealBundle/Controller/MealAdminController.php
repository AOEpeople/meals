<?php

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Event\WeekUpdateEvent;
use App\Mealz\MealBundle\Repository\DayRepositoryInterface;
use App\Mealz\MealBundle\Repository\DishRepository;
use App\Mealz\MealBundle\Repository\DishRepositoryInterface;
use App\Mealz\MealBundle\Repository\MealRepositoryInterface;
use App\Mealz\MealBundle\Repository\WeekRepositoryInterface;
use App\Mealz\MealBundle\Service\DayService;
use App\Mealz\MealBundle\Service\WeekService;
use App\Mealz\MealBundle\Validator\Constraints\DishConstraint;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\UnitOfWork;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * @Security("is_granted('ROLE_KITCHEN_STAFF')")
 */
class MealAdminController extends BaseController
{
    private EventDispatcherInterface $eventDispatcher;
    private WeekRepositoryInterface $weekRepository;
    private DishRepositoryInterface $dishRepository;
    private MealRepositoryInterface $mealRepository;
    private DayRepositoryInterface $dayRepository;
    private DayService $dayService;
    private EntityManagerInterface $em;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        WeekRepositoryInterface $weekRepository,
        DishRepositoryInterface $dishRepository,
        MealRepositoryInterface $mealRepository,
        DayRepositoryInterface $dayRepository,
        DayService $dayService,
        EntityManagerInterface $em
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->weekRepository = $weekRepository;
        $this->dishRepository = $dishRepository;
        $this->mealRepository = $mealRepository;
        $this->dayRepository = $dayRepository;
        $this->dayService = $dayService;
        $this->em = $em;
    }

    public function getWeeks(): JsonResponse
    {
        $weeks = [];
        $dateTime = new DateTime();

        for ($i = 0; $i < 8; ++$i) {
            $modifiedDateTime = clone $dateTime;
            $modifiedDateTime->modify('+' . $i . ' weeks');
            $week = $this->weekRepository->findOneBy(
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

        return new JsonResponse($weeks, 200);
    }

    /**
     * @Security("is_granted('ROLE_KITCHEN_STAFF')")
     */
    public function new(DateTime $date): JsonResponse
    {
        $week = $this->weekRepository->findOneBy([
            'year' => $date->format('o'),
            'calendarWeek' => $date->format('W'),
        ]);

        if (null !== $week) {
            return new JsonResponse(['status' => 'week already exists'], 400);
        }

        $dateTimeModifier = $this->getParameter('mealz.lock_toggle_participation_at');
        $week = WeekService::generateEmptyWeek($date, $dateTimeModifier);

        $this->em->persist($week);
        $this->em->flush();

        return new JsonResponse(['status' => 'success'], 200);
    }

    // TODO: still some work to be done here (enabled, date, notifications, etc.)
    public function edit(Request $request, Week $week): JsonResponse
    {
        // /** @var Week $week */
        // $week = $this->weekRepository->find($weekId);
        $data = json_decode($request->getContent(), true);

        if (null === $week || !isset($data) || !isset($data['days']) || !isset($data['id']) || $data['id'] !== $week->getId()) {
            return new JsonResponse(['status' => 'invalid json'], 400);
        }
        $days = $data['days'];

        // TODO: throw instead of code 400 then catch and return, also outsource parts to services
        foreach ($days as $day) {
            // check if day exists
            $dayEntity = $this->dayRepository->find($day['id']);
            if (null === $dayEntity) {
                return new JsonResponse(['status' => 'day not found'], 400);
            }

            $mealCollection = $day['meals'];
            // max 2 main meals allowed
            if (2 < count($mealCollection)) {
                return new JsonResponse(['status' => 'too many meals requested'], 400);
            }

            // TODO: check if it is even possible to set the day
            $this->dayService->removeUnusedMeals($dayEntity, $mealCollection);

            // parentMeal is an array of either one meal without variations or 1-2 variations
            foreach ($mealCollection as $parentDishId => $mealArr) {
                foreach ($mealArr as $meal) {
                    if (!isset($meal['dishSlug'])) {
                        continue;
                    }
                    $dishEntity = $this->dishRepository->findOneBy(['slug' => $meal['dishSlug']]);
                    if (null === $dishEntity) {
                        return new JsonResponse(['status' => 'dish not found for slug: ' . $meal['dishSlug']], 400);
                    }
                    // if mealId is null create meal
                    if (!isset($meal['mealId'])) {
                        $mealEntity = new Meal($dishEntity, $dayEntity);
                        // TODO: set Participation limit
                        $mealEntity->setParticipationLimit(0);
                        $dayEntity->addMeal($mealEntity);
                    } else {
                        // check if meal already exists and can be modified (aka has no participations)
                        $mealEntity = $this->mealRepository->find($meal['mealId']);
                        if (null !== $mealEntity && !$mealEntity->hasParticipations()) {
                            $mealEntity->setDish($dishEntity);
                            // TODO: Participation limit can also be set per meal
                            $mealEntity->setParticipationLimit(0);
                        } elseif (null === $mealEntity) {
                            // this happens because meals without participations are deleted, even though they could be modified later on (this shouldn't happen but might)
                            $mealEntity = new Meal($dishEntity, $dayEntity);
                            $mealEntity->setParticipationLimit(0);
                            $dayEntity->addMeal($mealEntity);
                        } else {
                            return new JsonResponse(['status' => 'meal has participations for id: ' . $meal['mealId']], 400);
                        }
                    }
                }
            }

            // if no meals exist, create and add new ones
            // if (0 === count($dayEntity->getMeals())) {
            //     foreach ($mealCollection as $meal) {
            //         if (isset($meal['dishSlug'])) {
            //             $dishEntity = $this->dishRepository->findOneBy(['slug' => $meal['dishSlug']]);
            //             if (null === $dishEntity) {
            //                 return new JsonResponse(['status' => 'dish not found'], 400);
            //             }
            //             $mealEntity = new Meal($dishEntity, $dayEntity);
            //             $mealEntity->setParticipationLimit($dishEntity->getParticipationLimit());
            //             $dayEntity->addMeal($mealEntity);
            //         }
            //     }
            //     continue;
            // }


            // foreach ($mealCollection as $meal) {
            //     // if meal already exists and has no participations, update dish
            //     if (isset($meal['mealId']) && $this->dayService->isMealInDay($day, $meal['mealId']) && !$this->dayService->mealHasParticipations($meal['mealId']) && !$this->dayService->isDishInDay($day, $meal['dishId'])) {
            //         $mealEntity = $this->mealRepository->find($meal['mealId']);
            //         $dishEntity = $this->dishRepository->find($meal['dishId']);
            //         $mealEntity->setDish($dishEntity);
            //     }
            //     // TODO: I forgot something...
            // }
        }

        $this->em->persist($week);
        $this->em->flush();

        return new JsonResponse(['status' => 'success'], 200);
    }

//    /**
//     * @return RedirectResponse|Response
//     *
//     * @throws OptimisticLockException|ORMException
//     *
//     * @Security("is_granted('ROLE_KITCHEN_STAFF')")
//     */
//    public function edit(Request $request, Week $week, DishRepository $dishRepository)
//    {
//        $dishes = $dishRepository->getSortedDishesQueryBuilder()->getQuery()->getResult();
//        $form = $this->createForm(WeekForm::class, $week);
//
//        // handle form submission
//        if (true === $request->isMethod('POST')) {
//            $form->handleRequest($request);
//
//            if (true === $form->get('Cancel')->isClicked()) {
//                return $this->redirectToRoute('MealzMealBundle_Meal');
//            }
//
//            if (true === $form->isValid()) {
//                $notify = $form->get('notifyCheckbox')->getData();
//                if (true === $this->updateWeek($week, $notify)) {
//                    $message = $this->get('translator')->trans('week.modified', [], 'messages');
//                    $this->addFlashMessage($message, 'success');
//                }
//            } else {
//                $errors = $form->getErrors(true);
//                foreach ($errors as $error) {
//                    if ($error->getCause() instanceof ConstraintViolation
//                        && $error->getCause()->getConstraint() instanceof DishConstraint
//                    ) {
//                        $translator = $this->get('translator');
//                        $messageTemplate = $error->getMessageTemplate();
//                        $messageParameters = $error->getMessageParameters();
//                        $day = $messageParameters['%day%'];
//                        $messageParameters['%day%'] = $translator->trans($day, [], 'date');
//                        $message = $translator->trans($messageTemplate, $messageParameters, 'messages');
//                        $this->addFlashMessage($message, 'danger');
//                    }
//                }
//            }
//
//            return $this->redirectToRoute(
//                'MealzMealBundle_Meal_edit',
//                ['week' => $week->getId()]
//            );
//        }
//
//        return $this->render(
//            'MealzMealBundle:MealAdmin:week.html.twig',
//            [
//                'dishes' => $dishes,
//                'week' => $week,
//                'form' => $form->createView(),
//            ]
//        );
//    }

//    /**
//     * @throws OptimisticLockException|ORMException
//     */
//    private function updateWeek(Week $week, bool $notify = false): bool
//    {
//        /** @var EntityManager $entityManager */
//        $entityManager = $this->getDoctrine()->getManager();
//
//        /** @var Day $day */
//        foreach ($week->getDays() as $day) {
//            /** @var Meal $meal */
//            foreach ($day->getMeals() as $meal) {
//                if (UnitOfWork::STATE_REMOVED === $entityManager->getUnitOfWork()->getEntityState($meal) && 0 < count($meal->getParticipants())) {
//                    $message = $this->get('translator')->trans(
//                        'error.meal.has_participants',
//                        [
//                            '%dish%' => $meal->getDish()->getTitle(),
//                            '%day%' => $day->getDateTime()->format('d.m'),
//                        ],
//                        'messages'
//                    );
//                    $this->addFlashMessage($message, 'danger');
//
//                    return false;
//                } elseif (UnitOfWork::STATE_REMOVED === $entityManager->getUnitOfWork()->getEntityState($meal)) {
//                    $day->removeMeal($meal);
//                }
//            }
//        }
//
//        $entityManager->persist($week);
//        $entityManager->flush();
//        $this->eventDispatcher->dispatch(new WeekUpdateEvent($week, $notify));
//
//        return true;
//    }
}
