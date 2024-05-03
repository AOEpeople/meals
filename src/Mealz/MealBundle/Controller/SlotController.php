<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Repository\ParticipantRepositoryInterface;
use App\Mealz\MealBundle\Repository\SlotRepositoryInterface;
use App\Mealz\MealBundle\Service\ParticipationService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Security("is_granted('ROLE_KITCHEN_STAFF')")
 */
class SlotController extends BaseListController
{
    private EntityManagerInterface $em;
    private SlotRepositoryInterface $slotRepo;
    private ParticipantRepositoryInterface $participantRepo;
    private ParticipationService $participationSrv;

    public function __construct(
        EntityManagerInterface $em,
        SlotRepositoryInterface $slotRepo,
        ParticipantRepositoryInterface $participantRepo,
        ParticipationService $participationSrv
    ) {
        $this->em = $em;
        $this->slotRepo = $slotRepo;
        $this->participantRepo = $participantRepo;
        $this->participationSrv = $participationSrv;
    }

    /**
     * Send TimeSlot Data.
     */
    public function getTimeSlots(): JsonResponse
    {
        $slots = $this->slotRepo->findBy(['deleted' => 0]);

        return new JsonResponse($slots, \Symfony\Component\HttpFoundation\Response::HTTP_OK);
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

            return new JsonResponse($slot, \Symfony\Component\HttpFoundation\Response::HTTP_OK);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(['message' => $e->getMessage()], \Symfony\Component\HttpFoundation\Response::HTTP_METHOD_NOT_ALLOWED);
        }
    }

    public function delete(Slot $slot): JsonResponse
    {
        try {
            $slot->setDeleted(true);

            $this->em->persist($slot);
            $this->em->flush();
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(['message' => $e->getMessage()], \Symfony\Component\HttpFoundation\Response::HTTP_METHOD_NOT_ALLOWED);
        }

        return new JsonResponse(null, \Symfony\Component\HttpFoundation\Response::HTTP_OK);
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
            $this->logException($e);

            return new JsonResponse(['message' => $e->getMessage()], \Symfony\Component\HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(null, \Symfony\Component\HttpFoundation\Response::HTTP_OK);
    }
}
