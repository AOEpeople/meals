<?php

namespace App\Mealz\MealBundle\Tests\Controller;

use App\Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadEvents;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Event;
use App\Mealz\MealBundle\Repository\DayRepositoryInterface;
use App\Mealz\MealBundle\Repository\EventParticipationRepositoryInterface;
use App\Mealz\MealBundle\Repository\ParticipantRepositoryInterface;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use DateTime;
use Doctrine\ORM\EntityManager;

class EventControllerTest extends AbstractControllerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadDays(),
            new LoadEvents(),
            new LoadRoles(),
            new LoadUsers(self::$container->get('security.user_password_encoder.generic')),
            new LoadWeeks(),
        ]);

        $this->loginAs(self::USER_KITCHEN_STAFF);
    }

    public function testGetEventList(): void
    {
        $this->client->request('GET', '/api/events');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        /** @var EntityManager $entityManager */
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $eventRepo = $entityManager->getRepository(Event::class);
        $events = $eventRepo->findBy(['deleted' => 0]);

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $foundAll = true;

        foreach ($response as $event) {
            $foundEvent = false;
            /** @var Event $repoEvent */
            foreach ($events as $repoEvent) {
                if ($event['slug'] === $repoEvent->getSlug()) {
                    $foundEvent = true;
                }
            }
            if (false === $foundEvent) {
                $foundAll = false;
                break;
            }
        }

        $this->assertTrue($foundAll, 'Did not find all events');
    }

    public function testNew(): void
    {
        $data = json_encode([
            'title' => 'TestEvent1234',
            'public' => true,
        ]);

        $this->client->request('POST', '/api/events', [], [], [], $data);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        /** @var EntityManager $entityManager */
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $eventRepo = $entityManager->getRepository(Event::class);
        $event = $eventRepo->findOneBy(['slug' => 'testevent1234']);

        $this->assertNotNull($event);
        $this->assertInstanceOf(Event::class, $event);
        $this->assertEquals('TestEvent1234', $event->getTitle());
    }

    public function testUpdate(): void
    {
        $newEvent = $this->createEvent();
        $this->persistAndFlushAll([$newEvent]);

        $data = json_encode([
            'title' => 'TestEvent666',
            'public' => true,
        ]);

        $this->client->request('PUT', '/api/events/' . $newEvent->getSlug(), [], [], [], $data);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        /** @var EntityManager $entityManager */
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $eventRepo = $entityManager->getRepository(Event::class);
        $event = $eventRepo->findOneBy(['title' => 'TestEvent666', 'public' => true]);

        $this->assertNotNull($event);
        $this->assertInstanceOf(Event::class, $event);
        $this->assertEquals('TestEvent666', $event->getTitle());
    }

    public function testDelete(): void
    {
        $newEvent = $this->createEvent();
        $this->persistAndFlushAll([$newEvent]);

        $this->client->request('DELETE', '/api/events/' . $newEvent->getSlug());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        /** @var EntityManager $entityManager */
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $eventRepo = $entityManager->getRepository(Event::class);
        $event = $eventRepo->findOneBy(['slug' => $newEvent->getSlug()]);

        $this->assertTrue($event->isDeleted());
    }

    public function testJoin(): void
    {
        $newEvent = $this->createEvent();
        $this->persistAndFlushAll([$newEvent]);

        $dayRepo = self::$container->get(DayRepositoryInterface::class);

        $criteria = new \Doctrine\Common\Collections\Criteria();
        $criteria->where(\Doctrine\Common\Collections\Criteria::expr()->gt('lockParticipationDateTime', new DateTime()));

        /** @var Day $day */
        $day = $dayRepo->matching($criteria)->get(0);
        $this->assertNotNull($day);

        $eventParticipation = $this->createEventParticipation($day, $newEvent);

        $url = '/api/events/participation/' . $day->getDateTime()->format('Y-m-d') . '%20' . $day->getDateTime()->format('H:i:s');
        $this->client->request('POST', $url);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), $this->client->getResponse());

        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertNotNull($content);

        $eventPartRepo = self::$container->get(EventParticipationRepositoryInterface::class);
        $eventPart = $eventPartRepo->findOneBy(['id' => $content->participationId]);
        $this->assertNotNull($eventPart);

        $partRepo = self::$container->get(ParticipantRepositoryInterface::class);

        /** @var Participant $part */
        $part = $partRepo->findOneBy(['event' => $eventPart->getId()]);
        $this->assertNotNull($part);

        $this->assertEquals(self::USER_KITCHEN_STAFF, $part->getProfile()->getUsername());
    }

    public function testLeave(): void
    {
        $newEvent = $this->createEvent();
        $this->persistAndFlushAll([$newEvent]);

        $dayRepo = self::$container->get(DayRepositoryInterface::class);

        $criteria = new \Doctrine\Common\Collections\Criteria();
        $criteria->where(\Doctrine\Common\Collections\Criteria::expr()->gt('lockParticipationDateTime', new DateTime()));

        /** @var Day $day */
        $day = $dayRepo->matching($criteria)->get(0);

        $eventParticipation = $this->createEventParticipation($day, $newEvent);

        $url = '/api/events/participation/' . $day->getDateTime()->format('Y-m-d') . '%20' . $day->getDateTime()->format('H:i:s');
        $this->client->request('POST', $url);

        $url = '/api/events/participation/' . $day->getDateTime()->format('Y-m-d') . '%20' . $day->getDateTime()->format('H:i:s');
        $this->client->request('DELETE', $url);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), $this->client->getResponse());

        $content = json_decode($this->client->getResponse()->getContent());
        $this->assertNotNull($content);

        $eventPartRepo = self::$container->get(EventParticipationRepositoryInterface::class);
        $eventPart = $eventPartRepo->findOneBy(['id' => $content->participationId]);
        $this->assertNotNull($eventPart);

        $partRepo = self::$container->get(ParticipantRepositoryInterface::class);

        /** @var Participant $part */
        $part = $partRepo->findOneBy(['event' => $eventPart->getId()]);
        $this->assertNull($part);
    }
}
