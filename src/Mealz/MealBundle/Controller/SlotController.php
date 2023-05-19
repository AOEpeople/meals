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
        $response = [
            'title' => $slot->getTitle(),
            'limit' => $slot->getLimit(),
            'order' => $slot->getOrder(),
            'enabled' => $slot->isEnabled(),
        ];

        return new JsonResponse($response, 200);
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

    public function deleteSlot(Request $request, SlotService $slotService): JsonResponse
    {
        try {
            $parameters = json_decode($request->getContent(), true);
            $slotService->delete($parameters);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(null, 500);
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

            return new JsonResponse(null, 500);
        }

        return new JsonResponse(['status' => 'success'], 200);
    }
}
