<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

class FrontendController extends BaseController
{
    public function renderIndex(): Response
    {
        // $baseUrl = $this->getParameter('app.base_url');
        // if ('oauth' === $this->getParameter('app.auth.mode') && null === $this->getUser()) {
        //     return $this->redirect($baseUrl . '/weeks');
        // } else if ('json' === $this->getParameter('app.auth.mode') && null === $this->getUser()){
        //     return $this->redirect($baseUrl . '/login');
        // }
        return $this->render('MealzMealBundle:Meals:index.html.twig');
    }
}
