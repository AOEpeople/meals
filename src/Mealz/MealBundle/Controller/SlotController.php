<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Repository\ParticipantRepositoryInterface;
use App\Mealz\MealBundle\Repository\SlotRepositoryInterface;
use App\Mealz\MealBundle\Service\ParticipationService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_KITCHEN_STAFF')]
class SlotController extends BaseListController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SlotRepositoryInterface $slotRepo,
        private readonly ParticipantRepositoryInterface $participantRepo,
        private readonly ParticipationService $participationSrv,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Send TimeSlot Data.
     */
    public function getTimeSlots(): JsonResponse
    {
        $slots = $this->slotRepo->findBy(['deleted' => 0]);

        return new JsonResponse($slots, Response::HTTP_OK);
    }

    public function update(Request $request, Slot $slot): JsonResponse
    {
        try {
            $parameters = json_decode($request->getContent(), true);
            if (true === isset($parameters['title'])) {
                $slot->setTitle($parameters['title']);
            }
            if (true === isset($parameters['limit'])) {
                $slot->setLimit($parameters['limit']);
            }
            if (true === isset($parameters['order'])) {
                $slot->setOrder($parameters['order']);
            }
            if (true === isset($parameters['enabled'])) {
                $slot->setDisabled(!$parameters['enabled']);
                if (true === $slot->isDisabled()) {
                    $participations = $this->participantRepo->getParticipationsOfSlot($slot);
                    $this->participationSrv->setParticipationSlotsEmpty($participations);
                    $participations = $this->participantRepo->getParticipationsOfSlot($slot);
                }
            }

            $this->em->persist($slot);
            $this->em->flush();

            return new JsonResponse($slot, Response::HTTP_OK);
        } catch (Exception $e) {
            $this->logger->info('slot update error', $this->getTrace($e));

            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_METHOD_NOT_ALLOWED);
        }
    }

    public function delete(Slot $slot): JsonResponse
    {
        try {
            $slot->setDeleted(true);

            $this->em->persist($slot);
            $this->em->flush();
        } catch (Exception $e) {
            $this->logger->info('slot delete error', $this->getTrace($e));

            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_METHOD_NOT_ALLOWED);
        }

        return new JsonResponse(null, Response::HTTP_OK);
    }

    /**
     * Creates a new slot.
     */
    public function new(Request $request): JsonResponse
    {
        try {
            $parameters = json_decode($request->getContent(), true);
            if (false === isset($parameters['title'])) {
                throw new Exception('Title is missing');
            } elseif ('' === $parameters['title']) {
                throw new Exception('Title is empty');
            }
            $slot = new Slot();
            $slot->setTitle($parameters['title']);
            if (true === isset($parameters['limit'])) {
                $slot->setLimit($parameters['limit']);
            }
            if (true === isset($parameters['order'])) {
                $slot->setOrder($parameters['order']);
            }

            $this->em->persist($slot);
            $this->em->flush();
        } catch (Exception $e) {
            $this->logger->info('slot create error', $this->getTrace($e));

            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(null, Response::HTTP_OK);
    }
}
