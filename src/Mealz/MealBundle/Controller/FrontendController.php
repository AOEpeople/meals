<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

class FrontendController extends BaseController
{
    public function renderIndex(): Response
    {
        return $this->render('@MealzMeal/Meals/index.html.twig');
    }
}
