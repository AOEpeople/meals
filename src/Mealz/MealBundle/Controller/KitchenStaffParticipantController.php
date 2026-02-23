<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Helper\ParticipationHelper;
use App\Mealz\MealBundle\Repository\DayRepositoryInterface;
use App\Mealz\MealBundle\Repository\ParticipantRepositoryInterface;
use App\Mealz\MealBundle\Service\Doorman;
use App\Mealz\MealBundle\Service\EventService;
use App\Mealz\MealBundle\Service\ParticipationService;
use App\Mealz\UserBundle\Entity\Profile;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_KITCHEN_STAFF')]
final class KitchenStaffParticipantController extends BaseController
{
    use ParticipantLoggingTrait;

    public function __construct(
        private readonly EventService $eventSrv,
        private readonly ParticipationHelper $participationHelper,
        private readonly ParticipationService $participationSrv,
        private readonly DayRepositoryInterface $dayRepo,
        private readonly ParticipantRepositoryInterface $participantRepo,
        private readonly LoggerInterface $logger,
        private readonly Doorman $doorman
    ) {
    }

    public function getParticipationsForWeek(Week $week): JsonResponse
    {
        $days = $week->getDays();
        $response = [];

        /** @var Day $day */
        foreach ($days as $day) {
            $meals = $day->getMeals();
            $participants = new ArrayCollection();
            /** @var Meal $meal */
            foreach ($meals as $meal) {
                $participants = new ArrayCollection(array_merge($participants->toArray(), $meal->getParticipants()->toArray()));
            }

            $response = $this->addParticipationInfo($response, $participants, $day);
        }

        return new JsonResponse($response, Response::HTTP_OK);
    }

    public function add(EntityManagerInterface $entityManager, int $userid, Meal $meal, Request $request): JsonResponse
    {
        $profile = $entityManager->getRepository(Profile::class)->find($userid);
        if (!$profile) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $parameters = json_decode($request->getContent(), true);

        try {
            if (true === $meal->isCombinedMeal() && false === isset($parameters['combiDishes'])) {
                throw new Exception('401: Combined Meals need exactly two dishes');
            }

            if (true === isset($parameters['combiDishes'])) {
                $result = $this->participationSrv->join($profile, $meal, null, $parameters['combiDishes']);
            } else {
                $result = $this->participationSrv->join($profile, $meal);
            }

            $this->eventSrv->triggerJoinEvents($result['participant'], $result['offerer']);
            $this->logAdd($meal, $result['participant']);

            // get updated day
            $day = $this->dayRepo->getDayByDate($meal->getDay()->getDateTime());
            $participations = $this->participationSrv->getParticipationsByDayAndProfile($profile, $day);

            $participationData = [];
            foreach ($participations as $participation) {
                $participationData[] = $this->participationHelper->getParticipationMealData($participation);
            }

            return new JsonResponse([
                'day' => $meal->getDay()->getId(),
                'profile' => $profile->getId(),
                'booked' => $participationData,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            $this->logger->info('error adding participant to meal', $this->getTrace($e));

            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function remove(EntityManagerInterface $entityManager, int $userid, Meal $meal): JsonResponse
    {
        $profile = $entityManager->getRepository(Profile::class)->find($userid);
        if (!$profile) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $participation = $this->participationSrv->getParticipationByMealAndUser($meal, $profile);
            $participation->setCombinedDishes(null);

            $entityManager->remove($participation);
            $entityManager->flush();

            $this->eventSrv->triggerLeaveEvents($participation);
            $this->logRemove($meal, $participation);

            $participations = $this->participationSrv->getParticipationsByDayAndProfile($profile, $meal->getDay());

            $participationData = [];
            foreach ($participations as $participation) {
                $participationData[] = $this->participationHelper->getParticipationMealData($participation);
            }

            return new JsonResponse([
                'day' => $meal->getDay()->getId(),
                'profile' => $profile->getId(),
                'booked' => $participationData,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            $this->logger->info('error removing participant from meal', $this->getTrace($e));

            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getProfilesWithoutParticipation(Week $week): JsonResponse
    {
        $participations = $this->participantRepo->getParticipantsOnDays($week->getStartTime(), $week->getEndTime());
        $response = $this->participationHelper->getNonParticipatingProfilesByWeek($participations);

        return new JsonResponse($response, Response::HTTP_OK);
    }

    /**
     * @param array<mixed> $response
     *
     * @return array<mixed>
     */
    private function addParticipationInfo(array $response, ArrayCollection $participants, Day $day): array
    {
        if (0 === count($participants)) {
            $response[$day->getId()] = new stdClass();

            return $response;
        }

        /** @var Participant $participant */
        foreach ($participants as $participant) {
            $participationData = $this->participationHelper->getParticipationMealData($participant);
            $response[$day->getId()][$participant->getProfile()->getId()]['booked'][] = $participationData;
            $response[$day->getId()][$participant->getProfile()->getId()]['fullName'] = $this->participationHelper->getParticipantName($participant);
        }

        return $response;
    }
}
