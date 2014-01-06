<?php

namespace Xopn\MealzForZombies\MealzBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Xopn\MealzForZombies\ZombiesBundle\Entity\Zombie;

/**
 * Dish
 *
 * @ORM\Table(name="participant")
 * @ORM\Entity
 */
class Participant
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Meal")
     * @ORM\JoinColumn(name="meal_id", referencedColumnName="id")
     * @var Meal
     */
    protected $meal;

    /**
     * @ORM\ManyToOne(targetEntity="Xopn\MealzForZombies\ZombiesBundle\Entity\Zombie")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @var Zombie
     */
    protected $user;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \Xopn\MealzForZombies\MealzBundle\Entity\Meal $meal
     */
    public function setMeal($meal)
    {
        $this->meal = $meal;
    }

    /**
     * @return \Xopn\MealzForZombies\MealzBundle\Entity\Meal
     */
    public function getMeal()
    {
        return $this->meal;
    }

    /**
     * @param \Xopn\MealzForZombies\ZombiesBundle\Entity\Zombie $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return \Xopn\MealzForZombies\ZombiesBundle\Entity\Zombie
     */
    public function getUser()
    {
        return $this->user;
    }


    function __toString()
    {
        return $this->getMeal() . ' ' . $this->getUser();
    }


}
