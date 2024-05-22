<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\Tests\EventSubscriber;

use App\Mealz\MealBundle\Tests\Controller\AbstractControllerTestCase;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use App\Mealz\UserBundle\EventSubscriber\InteractiveLoginSubscriber;
use App\Mealz\UserBundle\Repository\ProfileRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class InteractiveLoginSubscriberTest extends AbstractControllerTestCase
{
    private InteractiveLoginSubscriber $iaLoginSubscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();

        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = self::getContainer()->get('security.user_password_hasher');
        $this->loadFixtures([
            new LoadRoles(),
            new LoadUsers($passwordHasher),
        ]);

        /** @var EntityManager $entityManager */
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        /** @var ProfileRepositoryInterface $profileRepo */
        $profileRepo = self::getContainer()->get(ProfileRepositoryInterface::class);

        $this->iaLoginSubscriber = new InteractiveLoginSubscriber($entityManager, $profileRepo);
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
            ->method('getUserIdentifier')
            ->willReturn(parent::USER_STANDARD);

        $tokenInterfaceMock = $this->getMockBuilder(TokenInterface::class)
            ->getMock();
        $tokenInterfaceMock
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($userInterfaceMock);

        return new InteractiveLoginEvent(new Request(), $tokenInterfaceMock);
    }
}
