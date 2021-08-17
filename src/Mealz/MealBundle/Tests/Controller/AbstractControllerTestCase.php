<?php

namespace App\Mealz\MealBundle\Tests\Controller;

use DateTime;
use Doctrine\Common\Collections\Criteria;
use App\Mealz\AccountingBundle\Entity\Transaction;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\MealRepository;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Tests\AbstractDatabaseTestCase;
use App\Mealz\UserBundle\Entity\Profile;
use App\Mealz\UserBundle\Entity\Role;
use App\Mealz\UserBundle\Entity\RoleRepository;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractControllerTestCase extends AbstractDatabaseTestCase
{
    /**
     * Role based test users
     */
    protected const USER_STANDARD      = 'alice';
    protected const USER_FINANCE       = 'finance';
    protected const USER_KITCHEN_STAFF = 'kochomi';

    protected KernelBrowser $client;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    protected function loginAs(string $username): void
    {
        $session = $this->client->getContainer()->get('session');
        // the firewall context (defaults to the firewall name)
        $firewall = 'main';

        $repo = $this->client->getContainer()->get('doctrine')->getRepository('MealzUserBundle:Login');
        $user = $repo->findOneBy(['username' => $username]);

        if (!($user instanceof UserInterface)) {
            throw new RuntimeException('user not found: '.$username);
        }

        $token = new UsernamePasswordToken($user, null, $firewall, $user->getRoles());
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    protected function getFormCSRFToken(string $uri, string $tokenFieldSelector): string
    {
        $this->client->xmlHttpRequest('GET', $uri);
        $jsonEncodedForm = $this->client->getResponse()->getContent();
        $htmlForm = json_decode($jsonEncodedForm, false, 512, JSON_THROW_ON_ERROR);
        $crawler = new Crawler($htmlForm);
        $token = $crawler->filter($tokenFieldSelector)->attr('value');

        if ($token === '') {
            throw new RuntimeException('token fetch error: path: '.$uri.' : fieldSelector'.$tokenFieldSelector);
        }

        return $token;
    }

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
     * Create a default client with fincance role
     * @param array $options
     * @param array $server
     */
    protected function createFinanceClient($options = array(), $server = array())
    {
        $this->createAdminClient($options, $server, array('ROLE_FINANCE'));
    }

    /**
     * Create a client with a frontend user having a role ROLE_KITCHEN_STAFF
     *
     * @param array $options Array with symfony parameters to be set (e.g. environment,...)
     * @param array $server  Array with Server parameters to be set (e.g. HTTP_HOST,...)
     */
    protected function createAdminClient($options = array(), $server = array(), $roles = array('ROLE_KITCHEN_STAFF'))
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
        $firewall = 'main';

        $repo = $this->client->getContainer()->get('doctrine')->getRepository('MealzUserBundle:Login');
        $user = $repo->findOneBy(['username' => 'kochomi']);
        $user = ($user instanceof UserInterface) ? $user : 'kochomi';

        $token = new UsernamePasswordToken($user, null, $firewall, $roles);
        if (($session->getId()) === "") {
            $session->set('_security_'.$firewall, serialize($token));
            $session->save();
        }
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    /**
     * @param $userProfile
     */
    public function loginAsDefaultClient($userProfile)
    {
        //test for non-admin users
        $this->createDefaultClient();

        // Open home page and log in as user
        $crawler = $this->client->request('GET', '/');
        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'Requesting homepage failed');

        $loginForm = $crawler->filterXPath('//form[@name="login-form"]')
            ->form(
                [
                    '_username' => $userProfile->getUsername(),
                    '_password' => $userProfile->getUsername()
                ]
            );
        $this->client->followRedirects();
        $this->client->submit($loginForm, []);
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
        /** @var RoleRepository $profileRepository */
        $profileRepository = $this->getDoctrine()->getRepository('MealzUserBundle:Profile');
        /** @var Profile $userProfile */
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
        /** @var RoleRepository $roleRepository */
        $roleRepository = $this->getDoctrine()->getRepository('MealzUserBundle:Role');
        /** @var Role $guestRole */
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
     * @param DateTime $dateTime
     *
     * @return Meal
     */
    protected function getRecentMeal(DateTime $dateTime = null)
    {
        if ($dateTime === null) {
            $dateTime = new DateTime;
        }

        /** @var MealRepository $mealRepository */
        $mealRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Meal');
        $criteria = new Criteria();
        $criteria->create();
        $meals = $mealRepository->matching($criteria->where(Criteria::expr()->lte('dateTime', $dateTime)));

        if (1 > $meals->count()) {
            $this->fail('No test meal found.');
        }

        return $meals->first();
    }

    /**
     * Helper method to create a user transaction with specific amount and date
     * @param Profile $user
     * @param float $amount
     * @param DateTime|null $date
     */
    protected function createTransactions(Profile $user, $amount = 5.0, DateTime $date = null)
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
