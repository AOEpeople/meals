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
	private $name;

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
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
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
