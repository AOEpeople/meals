<?php

namespace Mealz\UserBundle\User;

use IMAG\LdapBundle\User\LdapUser as ImagLdapUser;
use Mealz\UserBundle\Entity\Profile;

class LdapUser extends ImagLdapUser implements UserInterface {

	/**
	 * @var Profile
	 */
	protected $profile;

	/**
	 * @param \Mealz\UserBundle\Entity\Profile|null $profile
	 */
	public function setProfile(Profile $profile = NULL)
	{
		$this->profile = $profile;
	}

	/**
	 * @return \Mealz\UserBundle\Entity\Profile
	 */
	public function getProfile()
	{
		return $this->profile;
	}
}