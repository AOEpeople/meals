<?php

namespace App\Mealz\MealBundle\Tests\Controller;

use App\Mealz\MealBundle\DataFixtures\ORM\LoadEvents;
use App\Mealz\MealBundle\Entity\Event;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use Doctrine\ORM\EntityManager;

class EventControllerTest extends AbstractControllerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadEvents(),
            new LoadRoles(),
            new LoadUsers(self::$container->get('security.user_password_encoder.generic')),
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
}
