<?php
/**
 * Created by PhpStorm.
 * User: jonathan.klauck
 * Date: 29.06.2016
 * Time: 15:45
 */

namespace Mealz\MealBundle\Tests\Controller;

use Doctrine\Common\Collections\Criteria;
use Mealz\AccountingBundle\Entity\Transaction;
use Mealz\MealBundle\Entity\Meal;
use Mealz\MealBundle\Entity\Participant;
use Mealz\MealBundle\Tests\AbstractDatabaseTestCase;
use Mealz\UserBundle\Entity\Profile;
use Mealz\UserBundle\Entity\Role;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class AbstractControllerTestCase
 * @package Mealz\MealBundle\Tests\Controller
 */
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
        // the firewall context (defaults to the firewall name)
        $firewall = 'mealz';

        $repo = $this->client->getContainer()->get('doctrine')->getRepository('MealzUserBundle:Login');
        $user = $repo->findOneBy(['username' => 'kochomi']);
        $user = ($user instanceof UserInterface) ? $user : 'kochomi';

        $token = new UsernamePasswordToken($user, null, $firewall, array('ROLE_KITCHEN_STAFF'));
        if (($session->getId()) === false) {
            $session->set('_security_'.$firewall, serialize($token));
            $session->save();
        }
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    /**
     * Gets a user profile.
     *
     * @param string $username Username. Default is 'alice'
     *
     * @return Profile
     */
    protected function getUserProfile($username = 'alice')
    {
        /** @var \Mealz\UserBundle\Entity\RoleRepository $profileRepository */
        $profileRepository = $this->getDoctrine()->getRepository('MealzUserBundle:Profile');
        /** @var \Mealz\UserBundle\Entity\Profile $userProfile */
        $userProfile = $profileRepository->findOneBy(['username' => $username]);

        if (false === ($userProfile instanceof Profile)) {
            $this->fail('User profile for "'.$username.'" not found.');
        }

        return $userProfile;
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
     * Helper method to get a user role.
     *
     * @param  string $roleType     Role string identifier i.e. sid.
     *
     * @return Role
     */
    protected function getRole($roleType)
    {
        /** @var \Mealz\UserBundle\Entity\RoleRepository $roleRepository */
        $roleRepository = $this->getDoctrine()->getRepository('MealzUserBundle:Role');
        /** @var \Mealz\UserBundle\Entity\Role $guestRole */
        $role = $roleRepository->findOneBy(['sid' => $roleType]);
        if (!($role instanceof Role)) {
            $this->fail('User role not "'.$roleType.'" found.');
        }

        return $role;
    }

    /**
     * Helper method to create a new user profile object.
     *
     * @param  string $firstName    User first name
     * @param  string $lastName     User last name
     * @param  string $company      User company
     * @return Profile
     */
    protected function createProfile($firstName = '', $lastName = '', $company = '')
    {
        $firstName = (trim(strval($firstName)) !== '') ? $firstName : 'Test';
        $lastName = (trim(strval($lastName)) !== '') ? $lastName : 'User'.rand();
        $company = (trim(strval($company)) !== '') ? $company : rand()."";

        $profile = new Profile();
        $profile->setUsername($firstName.'.'.$lastName);
        $profile->setFirstName($firstName);
        $profile->setName($lastName);

        if (trim(strval($company)) !== '') {
            $profile->setCompany($company);
        }

        return $profile;
    }

    /**
     * Helper method to create a new participant object.
     *
     * @param  Profile $profile     User profile
     * @param  Meal $meal           Meal instance
     *
     * @return Participant
     */
    protected function createParticipant($profile, $meal)
    {
        $participant = new Participant();
        $participant->setProfile($profile);
        $participant->setMeal($meal);

        $this->persistAndFlushAll([$participant]);

        return $participant;
    }

    /**
     * Helper method to get the recent meal.
     *
     * @return Meal
     */
    protected function getRecentMeal()
    {
        /** @var \Mealz\MealBundle\Entity\MealRepository $mealRepository */
        $mealRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Meal');
        $criteria = Criteria::create();
        $meals = $mealRepository->matching($criteria->where(Criteria::expr()->lte('dateTime', new \DateTime())));

        if (1 > $meals->count()) {
            $this->fail('No test meal found.');
        }

        return $meals->first();
    }

    /**
     * Helper method to create a user transaction with specific amount and date
     * @param Profile $user
     * @param float $amount
     * @param \DateTime|null $date
     */
    protected function createTransactions(Profile $user, $amount = 5.0, \DateTime $date = null)
    {
        $transaction = new Transaction();
        $amount = filter_var($amount, FILTER_VALIDATE_FLOAT, array('options' => array('min_range' => 0.1, 'default' => mt_rand(1000, 5000) / 100)));
        $transaction->setAmount($amount);
        $transaction->setProfile($user);
        $transaction->setDate($date);
        $this->persistAndFlushAll([$transaction]);
    }

    /**
     * mock the Flash Bag
     */
    private function mockFlashBag()
    {
        // Mock session.storage for flashbag
        $session = new Session(new MockFileSessionStorage());
        $this->client->getContainer()->set('session', $session);
    }
}