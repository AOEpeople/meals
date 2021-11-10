<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Service\SlotService;
use Exception;
use InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Security("is_granted('ROLE_KITCHEN_STAFF')")
 */
class SlotController extends BaseListController
{
    public function updateState(Request $request, SlotService $slotService, Slot $slot): JsonResponse
    {
        $state = (string) $request->request->get('disabled');

        try {
            $slotService->updateState($slot, $state);
        } catch (InvalidArgumentException $e) {
            return new JsonResponse(null, 422);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(null, 500);
        }

        return new JsonResponse(['status' => 'success']);
    }
}
