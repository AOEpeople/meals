<?php

namespace Mealz\MealBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Cookie;

class LanguageController extends BaseController
{

    /**
     * Action to switch between languages
     */
    public function switchAction(Request $request)
    {
        $referrer = $request->headers->get('referer');
        $response = new RedirectResponse($referrer);

        $preferredLanguage = ($request->getLocale() == 'en') ? 'de' : 'en';

        $cookie = new Cookie('locale', $preferredLanguage, time()+60*60*24*365);
        $response->headers->setCookie($cookie);

        return $response;
    }
}