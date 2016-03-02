<?php

namespace Mealz\UserBundle\EventListener;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class SecurityListener
{
    protected $router;
    protected $security;
    protected $dispatcher;

    public function __construct(Router $router, AuthorizationChecker $security, EventDispatcherInterface $dispatcher)
    {
        $this->router = $router;
        $this->security = $security;
        $this->dispatcher = $dispatcher;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $this->dispatcher->addListener(KernelEvents::RESPONSE, array($this, 'onKernelResponse'));
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if ($this->security->isGranted('ROLE_USER')) {
            $response = new RedirectResponse($this->router->generate('MealzMealBundle_home'));
        } elseif ($this->security->isGranted('ROLE_CONFIRMATION')) {
            $response = new RedirectResponse($this->router->generate('MealzMealBundle_Participation_confirm_index'));
        } else {
            $response = new RedirectResponse($this->router->generate('MealzMealBundle_home'));
        }

        $event->setResponse($response);
    }
}
