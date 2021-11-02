<?php

declare(strict_types=1);

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
use Exception;
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
    protected const USER_STANDARD      = 'alice.meals';
    protected const USER_FINANCE       = 'finance.meals';
    protected const USER_KITCHEN_STAFF = 'kochomi.meals';

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

        $repo = $this->client->getContainer()->get('doctrine')->getRepository('MealzUserBundle:Profile');
        $user = $repo->find($username);

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

        if ($token === '' || $token === null) {
            throw new RuntimeException('token fetch error, path: '.$uri.', fieldSelector: '.$tokenFieldSelector);
        }

        return $token;
    }

    /**
     * Gets a user profile.
     */
    protected function getUserProfile(string $username): Profile
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
     */
    protected function createParticipant(Profile $profile, Meal $meal): Participant
    {
        $participant = new Participant($profile, $meal);
        $this->persistAndFlushAll([$participant]);

        return $participant;
    }

    /**
     * Helper method to get the recent meal.
     */
    protected function getRecentMeal(DateTime $dateTime = null): Meal
    {
        if ($dateTime === null) {
            $dateTime = new DateTime;
        }

        /** @var MealRepository $mealRepository */
        $mealRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Meal');
        $criteria = Criteria::create();
        $criteria
            ->where(Criteria::expr()->lte('dateTime', $dateTime))
            ->orderBy(['dateTime' => Criteria::DESC]);
        $meals = $mealRepository->matching($criteria);

        if (1 > $meals->count()) {
            $this->fail('No test meal found.');
        }

        return $meals->first();
    }

    /**
     * @return Meal[]
     *
     * @throws Exception
     */
    protected function getLockedMeals(): array
    {
        $mealsRepo = $this->getDoctrine()->getRepository('MealzMealBundle:Meal');

        /** @var Meal[] $meals */
        $meals = $mealsRepo->getLockedMeals();
        if (0 < count($meals)) {
            return $meals;
        }

        // got no locked meals, modify existing meals to make few available
        $criteria = new Criteria(Criteria::expr()->gt('dateTime', new DateTime()), ['dateTime' => Criteria::ASC]);
        $meals = $mealsRepo->matching($criteria);

        if (1 > count($meals)) {
            throw new Exception('could not fake locked meal');
        }

        $mealsDay = $meals->first()->getDay();
        $mealsDay->setLockParticipationDateTime(new DateTime('- 30 minutes'));
        $this->persistAndFlushAll([$mealsDay]);

        return $meals->toArray();
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
