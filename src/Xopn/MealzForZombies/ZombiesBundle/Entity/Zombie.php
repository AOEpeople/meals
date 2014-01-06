<?php

namespace Xopn\MealzForZombies\ZombiesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Zombie
 *
 * @ORM\Table(name="zombie")
 * @ORM\Entity
 */
class Zombie
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="string", length=255, nullable=FALSE)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255, nullable=TRUE)
     * @var string
     */
    protected $name;

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

}
