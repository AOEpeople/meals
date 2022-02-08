<?php

namespace App\Mealz\MealBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;

class PublishController extends AbstractController
{
    /**
     * @Route("/publish", name="publish")
     */
    public function publish(HubInterface $hub): Response
    {
        $update = new Update(
            '/test',
            json_encode(['message' => 'test'])
        );

        $hub->publish($update);

        return new Response('testing');
    }
}