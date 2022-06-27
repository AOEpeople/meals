<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class TestController extends AbstractController
{
    public function test(): Response
    {
        return $this->render('MealzMealBundle:Test:index.html.twig');
    }
}
