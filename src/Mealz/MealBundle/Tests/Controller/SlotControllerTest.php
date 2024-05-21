<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Controller;

use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;

class SlotControllerTest extends AbstractControllerTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadRoles(),
            new LoadUsers(self::getContainer()->get('security.user_password_hasher')),
        ]);
    }

    /**
     * @test
     *
     * @testdox An enabled slot can be disabled.
     */
    public function disableSlot(): void
    {
        $this->markTestSkipped('frontend test');
        $this->loginAs(self::USER_KITCHEN_STAFF);

        $slot = $this->createSlot();
        self::assertFalse($slot->isDisabled());

        $this->client->request('POST', '/meal/slot/'.$slot->getSlug().'/update-state', ['disabled' => 1]);
        self::assertResponseIsSuccessful();

        $this->getDoctrine()->getManager()->refresh($slot);
        self::assertTrue($slot->isDisabled());
    }

    /**
     * @test
     *
     * @testdox A disabled slot can be enabled.
     */
    public function enableSlot(): void
    {
        $this->markTestSkipped('frontend test');
        $this->loginAs(self::USER_KITCHEN_STAFF);

        $slot = $this->createSlot(true);
        self::assertTrue($slot->isDisabled());

        $this->client->request('POST', '/meal/slot/'.$slot->getSlug().'/update-state', ['disabled' => 0]);
        self::assertResponseIsSuccessful();

        $this->getDoctrine()->getManager()->refresh($slot);
        self::assertFalse($slot->isDisabled());
    }

    /**
     * @test
     *
     * @testdox Invoking SlotController::updateState without login redirects user to login page.
     */
    public function updateStateFailureNotLoggedIn(): void
    {
        $this->markTestSkipped('frontend test');
        $this->client->request('POST', '/meal/slot/test/update-state');
        self::assertResponseStatusCodeSame(302);
    }

    /**
     * @test
     *
     * @testWith ["DELETE", 405]
     *           ["GET",    405]
     *           ["PUT",    405]
     *
     * @testdox Invoking SlotController::updateState with HTTP method $method returns HTTP status $expHttpStatus.
     */
    public function updateStateFailureWrongHTTPMethod(string $method, int $expHttpStatus): void
    {
        $this->markTestSkipped('frontend test');
        $this->loginAs(self::USER_KITCHEN_STAFF);

        $this->client->request($method, '/meal/slot/test/update-state');
        self::assertResponseStatusCodeSame($expHttpStatus);
    }

    /**
     * @test
     *
     * @testdox A slot can be deleted.
     */
    public function deleteSlot(): void
    {
        $this->markTestSkipped('frontend test');
        $this->loginAs(self::USER_KITCHEN_STAFF);

        $slot = $this->createSlot();
        self::assertFalse($slot->isDeleted());

        $this->client->request('DELETE', '/meal/slot/'.$slot->getSlug().'/delete');
        self::assertResponseIsSuccessful();

        $this->getDoctrine()->getManager()->refresh($slot);
        self::assertTrue($slot->isDeleted());
    }

    private function createSlot(bool $disabled = false): Slot
    {
        $slot = new Slot();
        $slot->setTitle('lorem ipsum');
        $slot->setDisabled($disabled);

        $this->persistAndFlushAll([$slot]);

        return $slot;
    }
}
