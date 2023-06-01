<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Service\SlotService;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Security("is_granted('ROLE_KITCHEN_STAFF')")
 */
class SlotController extends BaseListController
{
    public function updateSlot(Request $request, Slot $slot, SlotService $slotService): JsonResponse
    {
        try {
            $parameters = json_decode($request->getContent(), true);
            $slot = $slotService->updateSlot($parameters, $slot);

            return new JsonResponse($slot, 200);
        } catch (Exception $e) {
            return new JsonResponse(['status' => $e->getMessage()], 405);
        }
    }

    public function deleteSlot(Slot $slot, SlotService $slotService): JsonResponse
    {
        try {
            $slotService->delete($slot);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(['status' => $e->getMessage()], 405);
        }

        return new JsonResponse(['status' => 'success']);
    }

    public function createSlot(Request $request, SlotService $slotService): JsonResponse
    {
        try {
            $parameters = json_decode($request->getContent(), true);
            $slotService->createSlot($parameters);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(['status' => $e->getMessage()], 500);
        }

        return new JsonResponse(['status' => 'success'], 200);
    }
}
