<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\Tests\Listener;

use App\Mealz\MealBundle\Tests\Controller\AbstractControllerTestCase;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use App\Mealz\UserBundle\Entity\Profile;
use App\Mealz\UserBundle\Entity\ProfileRepository;
use App\Mealz\UserBundle\EventListener\InteractiveLoginListener;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class InteractiveLoginListenerTest extends AbstractControllerTestCase
{
    private InteractiveLoginListener $interactiveLoginListener;

    /**
     * Set up the testing environment
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

        /** @var ProfileRepository $profileRepo */
        $profileRepo = $this->getDoctrine()->getRepository(Profile::class);

        $this->interactiveLoginListener = new InteractiveLoginListener($entityManager, $profileRepo);
    }

    protected function tearDown(): void
    {
        $this->clearAllTables();
    }

    public function testOnSecurityInteractiveLoginWithNonHiddenUser()
    {
        $profile = $this->getUserProfile(parent::USER_STANDARD);
        $this->assertFalse($profile->isHidden());

        $this->interactiveLoginListener->onSecurityInteractiveLogin($this->getMockedInteractiveLoginEvent());

        $profile = $this->getUserProfile(parent::USER_STANDARD);
        $this->assertFalse($profile->isHidden());
    }

    public function testOnSecurityInteractiveLoginWithHiddenUser()
    {
        $profile = $this->getUserProfile(parent::USER_STANDARD);
        $this->assertNotNull($profile);

        $profile->setHidden(true);

        $this->persistAndFlushAll([$profile]);

        $profile = $this->getUserProfile(parent::USER_STANDARD);
        $this->assertTrue($profile->isHidden());

        $this->interactiveLoginListener->onSecurityInteractiveLogin($this->getMockedInteractiveLoginEvent());

        $profile = $this->getUserProfile(parent::USER_STANDARD);
        $this->assertFalse($profile->isHidden());
    }

    /**
     * Helper to get a mocked InteractiveLoginEvent
     *
     * @param string $username
     * @return InteractiveLoginEvent
     */
    private function getMockedInteractiveLoginEvent(string $username = parent::USER_STANDARD): InteractiveLoginEvent
    {
        $userInterfaceMock = $this->getMockBuilder(UserInterface::class)
            ->getMock();
        $userInterfaceMock->expects($this->once())
            ->method('getUsername')
            ->willReturn($username);

        $tokenInterfaceMock = $this->getMockBuilder(TokenInterface::class)
            ->getMock();
        $tokenInterfaceMock
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($userInterfaceMock);

        $interactiveLoginEventMock = $this->getMockBuilder(InteractiveLoginEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $interactiveLoginEventMock
            ->expects($this->once())
            ->method('getAuthenticationToken')
            ->willReturn($tokenInterfaceMock);
        return $interactiveLoginEventMock;
    }
}
