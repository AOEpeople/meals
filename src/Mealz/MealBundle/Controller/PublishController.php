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
            json_encode(['message' => 'test']),
            false,
            null,
            null,
            null
            
        );
        $result = $hub->publish($update);

        echo $result;
        return new Response(nl2br ("
            HUB: \n
            internal URL:   {$hub->getUrl()} \n
            public URL:     {$hub->getPublicURL()}\n\n
            Update:\n
            topic:          {$update->getTopics()[0]} \n
            payload:        {$update->getData()} \n
            private?:       {$update->isPrivate()} \n
            ID:             {$update->getId()} \n
            Type:           {$update->getType()} \n
            retry?:         {$update->getRetry()} \n\n
            publish result: {$result}"));
    }
}