<?php


namespace Mealz\UserBundle\EventListener;


use IMAG\LdapBundle\Event\LdapTokenEvent;
use IMAG\LdapBundle\Event\LdapUserEvent;
use Mealz\UserBundle\Service\PostLogin;
use Mealz\UserBundle\User\LdapUser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listen to LdapEvents to assign proper roles and create a user profile if the user
 * logged in for the first time
 */
class AuthenticationListener implements EventSubscriberInterface {

	public static function getSubscribedEvents() {
		return array(
			\IMAG\LdapBundle\Event\LdapEvents::PRE_BIND => 'onLdapPreBind',
			\IMAG\LdapBundle\Event\LdapEvents::POST_BIND => 'onLdapPostBind',
		);
	}

	/**
	 * @var PostLogin
	 */
	protected $postLoginService;

	public function __construct(PostLogin $postLoginService) {
		$this->postLoginService = $postLoginService;
	}

	/**
	 * Modifies the User before binding data from LDAP
	 *
	 * @param LdapUserEvent $event
	 * @throws \Exception
	 */
	public function onLdapPreBind(LdapUserEvent $event) {
		$user = $event->getUser();

		// mark the user as ldap user
		$user->addRole('ROLE_LOGIN_LDAP');
		$user->addRole('ROLE_USER');

	}

	/**
	 * Modifies the Token after binding data from LDAP
	 *
	 * This is called once a user has successfully authenticated via LDAP.
	 * Every uncatched exception thrown here will prevent the user from logging in.
	 *
	 * @param LdapTokenEvent $event
	 */
	public function onLdapPostBind(LdapTokenEvent $event) {
		$ldapToken = $event->getToken();
		/** @var LdapUser $user */
		$user = $ldapToken->getUser();

		$this->postLoginService->assureProfileExists($user);
	}
}