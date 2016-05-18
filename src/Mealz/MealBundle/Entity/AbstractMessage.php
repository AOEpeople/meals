<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

abstract class AbstractMessage
{
    /**
     * @ORM\Column(type="boolean", nullable=FALSE)
     * @var bool
     */
    private $disabled = FALSE;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min=8, max=255)
     * @ORM\Column(type="string", length=255, nullable=FALSE)
     * @var string
     */
    private $message;

    /**
     * @return boolean
     */
    public function isDisabled()
    {
        return $this->disabled;
    }

    /**
     * @param boolean $disabled
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }
}