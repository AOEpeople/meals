<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Service\Doorman;
use App\Mealz\MealBundle\Service\EventService;
use App\Mealz\MealBundle\Service\ParticipationService;
use App\Mealz\UserBundle\Entity\Profile;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
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
        private readonly ParticipationService $participationSrv,
        private readonly LoggerInterface $logger,
        private readonly Doorman $doorman
    ) {
    }

    public function getParticipationsForWeek(Week $week): JsonResponse
    {
        $response = $this->participationSrv->getParticipationsForWeek($week);

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

            // get updated day participations for user
            $day = $meal->getDay();
            $participationData = $this->participationSrv->getProfileParticipationsForDay($profile, $day);

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

            $day = $meal->getDay();
            $participationData = $this->participationSrv->getProfileParticipationsForDay($profile, $day);

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
        $response = $this->participationSrv->getNonParticipatingProfilesForWeek($week);

        return new JsonResponse($response, Response::HTTP_OK);
    }
}
