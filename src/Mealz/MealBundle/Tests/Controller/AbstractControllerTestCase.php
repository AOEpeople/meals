<?php
/**
 * Created by PhpStorm.
 * User: jonathan.klauck
 * Date: 29.06.2016
 * Time: 15:45
 */

namespace Mealz\MealBundle\Tests\Controller;

use Mealz\MealBundle\Tests\AbstractDatabaseTestCase;
use Mealz\UserBundle\Entity\Login;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use Symfony\Component\Security\Core\SecurityContext;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractControllerTestCase extends AbstractDatabaseTestCase
{
    /** @var  Client $client */
    protected $client;

    /**
     * Create a default client with no frontend user logged in
     *
     * @param array $options Array with symfony parameters to be set (e.g. environment,...)
     * @param array $server Array with Server parameters to be set (e.g. HTTP_HOST,...)
     */
    protected function createDefaultClient($options = array(), $server = array())
    {
        $defaultOptions = array(
            'environment' => 'test',
        );

        $defaultServer = array(
            'HTTP_ACCEPT_LANGUAGE' => 'en',
        );

        $options = array_merge($defaultOptions, $options);
        $server = array_merge($defaultServer, $server);

        $this->client = self::createClient($options, $server);
    }

    /**
     * Create a client with a frontend user having a role ROLE_KITCHEN_STAFF
     *
     * @param array $options Array with symfony parameters to be set (e.g. environment,...)
     * @param array $server Array with Server parameters to be set (e.g. HTTP_HOST,...)
     */
    protected function createAdminClient($options = array(), $server = array())
    {
        $this->createDefaultClient($options, $server);

        /**
         * If you encounter problems during testing with the session saying "Cannot set session ID after the session has started"
         * consider to run phpunit with the following property setting:
         *      processIsolation="true"
         * @see https://git.aoesupport.com/gitweb/project/concar/calimero/symfony.git/blob/HEAD:/app/phpunitFunctional.xml?js=1
         */
        $session = $this->client->getContainer()->get('session');
        #$session->migrate(true);
        // the firewall context (defaults to the firewall name)
        $firewall = 'mealz';

        $repo = $this->client->getContainer()->get('doctrine')->getRepository('MealzUserBundle:Login');
        $user = $repo->findOneBy(['username' => 'kochomi']);
        $user = ($user instanceof UserInterface) ? $user : 'kochomi';

        $token = new UsernamePasswordToken($user, null, $firewall, array('ROLE_KITCHEN_STAFF'));
        if (!$session->getId()) {
            $session->set('_security_'.$firewall, serialize($token));
            $session->save();
        }
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    /**
     * @param array $options
     */
    protected function mockServices($options = array())
    {
        $defaultOptions = array(
            'mockFlashBag' => true,
        );

        $options = array_merge($defaultOptions, $options);

        if ($options['mockFlashBag']) {
            $this->mockFlashBag();
        }
    }

    /**
     *mock the Flash Bag
     */
    private function mockFlashBag()
    {
        // Mock session.storage for flashbag
        $session = new Session(new MockFileSessionStorage());
        $this->client->getContainer()->set('session', $session);
    }
}