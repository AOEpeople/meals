<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\Tests\EventSubscriber;

use App\Mealz\MealBundle\Tests\Controller\AbstractControllerTestCase;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use App\Mealz\UserBundle\EventSubscriber\InteractiveLoginSubscriber;
use App\Mealz\UserBundle\Repository\ProfileRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class InteractiveLoginSubscriberTest extends AbstractControllerTestCase
{
    private InteractiveLoginSubscriber $iaLoginSubscriber;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadRoles(),
            new LoadUsers(self::$container->get('security.user_password_encoder.generic')),
        ]);

        /** @var EntityManager $entityManager */
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        /** @var ProfileRepositoryInterface $profileRepo */
        $profileRepo = self::$container->get(ProfileRepositoryInterface::class);

        $this->iaLoginSubscriber = new InteractiveLoginSubscriber($entityManager, $profileRepo);
    }

    protected function tearDown(): void
    {
        $this->clearAllTables();
    }

    public function testOnSecurityInteractiveLoginWithNonHiddenUser(): void
    {
        $profile = $this->getUserProfile(parent::USER_STANDARD);
        $this->assertFalse($profile->isHidden());

        $this->iaLoginSubscriber->onInteractiveLogin($this->getMockedInteractiveLoginEvent());

        $profile = $this->getUserProfile(parent::USER_STANDARD);
        $this->assertFalse($profile->isHidden());
    }

    public function testOnSecurityInteractiveLoginWithHiddenUser(): void
    {
        $profile = $this->getUserProfile(parent::USER_STANDARD);
        $profile->setHidden(true);

        $this->persistAndFlushAll([$profile]);

        $profile = $this->getUserProfile(parent::USER_STANDARD);
        $this->assertTrue($profile->isHidden());

        $this->iaLoginSubscriber->onInteractiveLogin($this->getMockedInteractiveLoginEvent());

        $profile = $this->getUserProfile(parent::USER_STANDARD);
        $this->assertFalse($profile->isHidden());
    }

    /**
     * Helper to get a mocked InteractiveLoginEvent.
     */
    private function getMockedInteractiveLoginEvent(): InteractiveLoginEvent
    {
        $userInterfaceMock = $this->getMockBuilder(UserInterface::class)
            ->getMock();
        $userInterfaceMock->expects($this->once())
            ->method('getUsername')
            ->willReturn(parent::USER_STANDARD);

        $tokenInterfaceMock = $this->getMockBuilder(TokenInterface::class)
            ->getMock();
        $tokenInterfaceMock
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($userInterfaceMock);

        $iaLoginEventMock = $this->getMockBuilder(InteractiveLoginEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $iaLoginEventMock
            ->expects($this->once())
            ->method('getAuthenticationToken')
            ->willReturn($tokenInterfaceMock);

        return $iaLoginEventMock;
    }
}
