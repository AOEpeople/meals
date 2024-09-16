<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Controller;

use App\Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadEvents;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use App\Mealz\MealBundle\Repository\GuestInvitationRepository;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use Symfony\Component\HttpFoundation\Response;

class MealGuestControllerTest extends AbstractControllerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadWeeks(),
            new LoadDays(),
            new LoadCategories(),
            new LoadDishes(),
            new LoadDishVariations(),
            new LoadEvents(),
            new LoadMeals(),
            new LoadRoles(),
            new LoadUsers(self::getContainer()->get('security.user_password_hasher')),
        ]);

        $this->loginAs(self::USER_KITCHEN_STAFF);
    }

    public function testnewGuestEventInvitation(): void
    {
        $guestInvitationRepo = self::getContainer()->get(GuestInvitationRepository::class);
        $eventParticipation = $this->createFutureEvent();
        $url = '/event/invitation/' . $eventParticipation->getDay()->getId() . $eventParticipation->getId();

        $this->client->request('GET', $url);
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $guestLink = json_decode($this->client->getResponse()->getContent())->url;
        $prefix = 'http://localhost/guest/event/';
        $invitationId = str_ireplace($prefix, '', $guestLink);

        $invitation = $guestInvitationRepo->find($invitationId);
        $this->assertNotNull($invitation);
        $this->assertEquals(
            $eventParticipation->getEvent()->getTitle(),
            $invitation->getDay()->getEventParticipation()->getEvent()->getTitle()
        );
    }

    public function testGetEventInvitationData(): void
    {
        $guestInvitationRepo = self::getContainer()->get(GuestInvitationRepository::class);
        $eventParticipation = $this->createFutureEvent();
        $profile = $this->createProfile('Max', 'Mustermann' . time());
        $this->persistAndFlushAll([$profile]);
        $eventInvitation = $guestInvitationRepo->findOrCreateInvitation($profile, $eventParticipation->getDay());

        $this->client->request('GET', '/api/event/invitation/' . $eventInvitation->getId());
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(
            json_encode($eventParticipation->getDay()->getDateTime()),
            json_encode($content->date)
        );
        $this->assertEquals(
            json_encode($eventParticipation->getDay()->getLockParticipationDateTime()),
            json_encode($content->lockDate)
        );
        $this->assertEquals(
            $eventParticipation->getEvent()->getTitle(),
            $content->event
        );
    }

    public function testJoinEventAsGuest(): void
    {
        $guestInvitationRepo = self::getContainer()->get(GuestInvitationRepository::class);
        $eventParticipation = $this->createFutureEvent();
        $profile = $this->createProfile('Max', 'Mustermann' . time());
        $this->persistAndFlushAll([$profile]);
        $eventInvitation = $guestInvitationRepo->findOrCreateInvitation($profile, $eventParticipation->getDay());

        // with company
        $this->client->request(
            'POST',
            '/api/event/invitation/' . $eventInvitation->getId(),
            [],
            [],
            [],
            json_encode([
                'firstName' => 'John',
                'lastName' => 'Doe',
                'company' => 'District 9',
            ])
        );
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // without company
        $this->client->request(
            'POST',
            '/api/event/invitation/' . $eventInvitation->getId(),
            [],
            [],
            [],
            json_encode([
                'firstName' => 'Jane',
                'lastName' => 'Doe',
                'company' => null,
            ])
        );
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // without firstName
        $this->client->request(
            'POST',
            '/api/event/invitation/' . $eventInvitation->getId(),
            [],
            [],
            [],
            json_encode([
                'firstName' => null,
                'lastName' => 'Doe',
                'company' => 'District 9',
            ])
        );
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }
}
