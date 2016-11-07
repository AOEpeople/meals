<?php

namespace Mealz\UserBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Role entity.
 *
 * @package Mealz\MealBundle\Entity
 * @author Chetan Thapliyal <chetan.thapliyal@aoe.com>
 *
 * @ORM\Entity
 * @ORM\Table(name="role")
 * @ORM\Entity(repositoryClass="RoleRepository")
 */
class Role
{
	/**
	 * Constants for default roles
	 */
	const ROLE_KITCHEN_STAFF = 'ROLE_KITCHEN_STAFF';
	const ROLE_USER          = 'ROLE_USER';
	const ROLE_GUEST         = 'ROLE_GUEST';
	const ROLE_ADMIN         = 'ROLE_ADMIN';


	/**
	 * Role ID
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @var int
	 */
	private $id;

	/**
	 * Role name
	 *
	 * @ORM\Column(type="string")
	 * @Assert\NotBlank
	 * @var string
	 */
	private $title;

	/**
	 * Role string identifier
	 *
	 * @ORM\Column(type="string")
	 * @Assert\NotBlank
	 * @var string
	 */
	private $sid;

	/**
	 * @ORM\ManyToMany(targetEntity="Profile", mappedBy="roles")
	 * @var Collection
	 */
	private $profiles;

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * @return string
	 */
	public function getSid()
	{
		return $this->sid;
	}

	/**
	 * @param string $sid
	 */
	public function setSid($sid)
	{
		$this->sid = $sid;
	}


	/**
	 * @return Collection
	 */
	public function getProfiles()
	{
		return $this->profiles;
	}

	/**
	 * @param Collection $profiles
	 */
	public function setProfiles(Collection $profiles)
	{
		$this->profiles = $profiles;
	}
}
