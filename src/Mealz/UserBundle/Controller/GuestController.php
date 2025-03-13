<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\Controller;

use App\Mealz\MealBundle\Controller\BaseController;
use App\Mealz\UserBundle\Service\ProfileService;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class GuestController extends BaseController
{
    private ProfileService $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    #[IsGranted('ROLE_ADMIN')]
    public function createGuestProfile(Request $request): JsonResponse
    {
        try {
            $parameters = json_decode($request->getContent(), true);
            if (false === isset($parameters['firstName']) || false === isset($parameters['lastName'])) {
                throw new Exception('1001: Firstname or lastname missing');
            }

            $profile = $this->profileService->createGuest(
                $parameters['firstName'],
                $parameters['lastName'],
                isset($parameters['company']) ? $parameters['company'] : null
            );

            if ($profile['profile']->isHidden()) {
                throw new Exception('1002: The profile exists but is hidden');
            }

            return new JsonResponse($profile['profile'], 'new' === $profile['status'] ? Response::HTTP_CREATED : Response::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
