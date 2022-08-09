<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Repository\SlotRepository;
use App\Mealz\MealBundle\Service\SlotService;
use Exception;
use InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Security("is_granted('ROLE_KITCHEN_STAFF')")
 */
class SlotController extends BaseListController
{
    public function listSlots(SlotRepository $slotRepo): Response
    {
        $slots = $slotRepo->findBy(['deleted' => 0]);

        return $this->render('MealzMealBundle:Slot:list.html.twig', ['slots' => $slots]);
    }

    public function updateSlot(Request $request, SlotService $slotService): JsonResponse
    {
        $parameters = json_decode($request->getContent(), true);

        $slot = $slotService->updateSlot($parameters);

        return new JsonResponse(null);
    }

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

    public function deleteSlot(Slot $slot, SlotService $slotService): JsonResponse
    {
        try {
            $slotService->delete($slot);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(null, 500);
        }

        return new JsonResponse(['status' => 'success']);
    }
}
