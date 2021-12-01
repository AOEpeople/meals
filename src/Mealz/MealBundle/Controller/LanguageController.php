<?php

namespace App\Mealz\MealBundle\Controller;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class LanguageController extends BaseController
{
    /**
     * Action to switch between languages.
     */
    public function switch(Request $request): RedirectResponse
    {
        $referrer = $request->headers->get('referer');
        $response = new RedirectResponse($referrer);

        $preferredLanguage = ('en' === $request->getLocale()) ? 'de' : 'en';

        $cookie = Cookie::create('locale', $preferredLanguage, time() + 60 * 60 * 24 * 365);
        $response->headers->setCookie($cookie);

        return $response;
    }
}
