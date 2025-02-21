<?php

namespace App\Mealz\UserBundle\Controller;

use App\Mealz\AccountingBundle\Service\Wallet;
use App\Mealz\MealBundle\Controller\BaseController;
use App\Mealz\UserBundle\Entity\Role;
use Symfony\Component\HttpFoundation\JsonResponse;

final class UserController extends BaseController
{
    private Wallet $wallet;

    public function __construct(Wallet $wallet)
    {
        $this->wallet = $wallet;
    }

    public function getUserData(): JsonResponse
    {
        $profile = $this->getProfile();

        if (null === $profile) {
            return new JsonResponse(
                [
                    'roles' => [Role::ROLE_GUEST],
                    'name' => null,
                    'fullName' => null,
                    'balance' => 0,
                ]
            );
        }
        $response = $profile->jsonSerialize();
        $response['balance'] = $this->wallet->getBalance($profile);

        return new JsonResponse($response);
    }
}
