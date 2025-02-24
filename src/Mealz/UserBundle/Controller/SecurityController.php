<?php

namespace App\Mealz\UserBundle\Controller;

use Error;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

final class SecurityController extends AbstractController
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function login(Request $request): RedirectResponse
    {
        // If Keycloak is enabled, redirect to the Meals home
        $token = $this->tokenStorage->getToken();
        if ($token instanceof OAuthToken) {
            return $this->redirectToRoute('MealzMealBundle_home');
        }

        $session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(SecurityRequestAttributes::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                SecurityRequestAttributes::AUTHENTICATION_ERROR
            );
            throw new Error($error);
        } else {
            $session->remove(SecurityRequestAttributes::AUTHENTICATION_ERROR);
        }

        return $this->redirectToRoute('MealzMealBundle_home');
    }
}
