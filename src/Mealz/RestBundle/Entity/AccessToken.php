<?php

namespace App\Mealz\RestBundle\Entity;

use FOS\OAuthServerBundle\Entity\AccessToken as BaseAccessToken;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table("oauth2_access_tokens")
 * @ORM\Entity
 */
class AccessToken extends BaseAccessToken
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Mealz\RestBundle\Entity\Client")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $client;

    /**
     * @ORM\ManyToOne(targetEntity="App\Mealz\UserBundle\Entity\Login")
     */
    protected $user;
}
