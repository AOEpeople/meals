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
 * @Security("is_granted('ROLE_ADMIN')")
 */
class SlotController extends BaseListController
{
    public function update(Request $request, SlotService $slotService, Slot $slot): JsonResponse
    {
        if ('POST' !== $request->getMethod()) {
            return new JsonResponse(null, 405);
        }

        $data = $request->request->all();

        try {
            $slotService->update($slot, $data);
        } catch (InvalidArgumentException $e) {
            return new JsonResponse(null, 422);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(null, 500);
        }

        return new JsonResponse(['status' => 'success']);
    }
}
