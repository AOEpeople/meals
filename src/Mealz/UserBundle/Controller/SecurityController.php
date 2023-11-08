<?php

namespace App\Mealz\UserBundle\Controller;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

class SecurityController extends AbstractController
{
    public function loginAction(Request $request): Response
    {
        // If Keycloak is enabled, redirect to the Meals home
        $token = $this->get('security.token_storage')->getToken();
        if ($token instanceof OAuthToken) {
            return $this->redirectToRoute('MealzMealBundle_home');
        }

        $session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                Security::AUTHENTICATION_ERROR
            );
        } else {
            $error = $session->get(Security::AUTHENTICATION_ERROR);
            $session->remove(Security::AUTHENTICATION_ERROR);
        }

        return $this->redirectToRoute('MealzMealBundle_home');
    }
}
