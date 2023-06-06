<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Repository\SlotRepositoryInterface;
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

    public function __construct(
        EntityManagerInterface $em,
        SlotRepositoryInterface $slotRepo
    ) {
        $this->em = $em;
        $this->slotRepo = $slotRepo;
    }

    /**
     * Send TimeSlot Data.
     */
    public function getTimeSlots(): JsonResponse
    {
        $slots = $this->slotRepo->findBy(['deleted' => 0]);

        return new JsonResponse($slots, 200);
    }

    public function update(Request $request, Slot $slot): JsonResponse
    {
        try {
            $parameters = json_decode($request->getContent(), true);
            if (isset($parameters['title'])) {
                $slot->setTitle($parameters['title']);
            }
            if (isset($parameters['limit'])) {
                $slot->setLimit($parameters['limit']);
            }
            if (isset($parameters['order'])) {
                $slot->setOrder($parameters['order']);
            }
            if (isset($parameters['enabled'])) {
                $slot->setDisabled(!$parameters['enabled']);
            }

            $this->em->persist($slot);
            $this->em->flush();

            return new JsonResponse($slot, 200);
        } catch (Exception $e) {
            return new JsonResponse(['status' => $e->getMessage()], 405);
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

            return new JsonResponse(['status' => $e->getMessage()], 405);
        }

        return new JsonResponse(['status' => 'success']);
    }

    /**
     * Creates a new slot.
     */
    public function new(Request $request): JsonResponse
    {
        try {
            $parameters = json_decode($request->getContent(), true);
            if (!isset($parameters['title'])) {
                throw new Exception('Title is missing');
            } elseif ('' === $parameters['title']) {
                throw new Exception('Title is empty');
            }
            $slot = new Slot();
            $slot->setTitle($parameters['title']);
            if (isset($parameters['limit'])) {
                $slot->setLimit($parameters['limit']);
            }
            if (isset($parameters['order'])) {
                $slot->setOrder($parameters['order']);
            }

            $this->em->persist($slot);
            $this->em->flush();
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(['status' => $e->getMessage()], 500);
        }

        return new JsonResponse(['status' => 'success'], 200);
    }
}
