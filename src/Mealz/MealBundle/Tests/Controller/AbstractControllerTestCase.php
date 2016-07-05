<?php
/**
 * Created by PhpStorm.
 * User: jonathan.klauck
 * Date: 29.06.2016
 * Time: 15:45
 */

namespace Mealz\MealBundle\Tests\Controller;

use Mealz\MealBundle\Tests\AbstractDatabaseTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use Symfony\Component\Security\Core\SecurityContext;

abstract class AbstractControllerTestCase extends AbstractDatabaseTestCase
{
    /** @var  Client $client */
    protected $client;

    protected function createDefaultClient($options = array(), $server = array())
    {
        $defaultOptions = array(
            'environment' => 'test'
        );

        $defaultServer = array(
            'HTTP_ACCEPT_LANGUAGE' => 'en'
        );

        $options = array_merge($defaultOptions, $options);
        $server = array_merge($defaultServer, $server);

        $this->client = self::createClient($options, $server);
    }

    protected function createAdminClient($options = array(), $server = array())
    {
        $this->createDefaultClient($options, $server);

        // Mock security.context service: allow all for admin routes
        $securityContext = $this->getMockBuilder(SecurityContext::class)
            ->setMethods(
                array(
                    'isGranted'
                )
            )
            ->disableOriginalConstructor()
            ->getMock();

        $securityContext->expects($this->atLeastOnce())
            ->method('isGranted')
            ->with(
                $this->equalTo('ROLE_KITCHEN_STAFF'),
                $this->isNull()
            )
            ->will($this->returnValue(true));

        $this->client->getContainer()->set('security.context', $securityContext);
    }

    protected function mockServices($options = array())
    {
        $defaultOptions = array(
            'mockFlashBag' => true
        );

        $options = array_merge($defaultOptions, $options);

        if ($options['mockFlashBag']) {
            $this->mockFlashBag();
        }
    }

    private function mockFlashBag()
    {
        // Mock session.storage for flashbag
        $session = new Session(new MockFileSessionStorage());
        $this->client->getContainer()->set('session', $session);
    }
}