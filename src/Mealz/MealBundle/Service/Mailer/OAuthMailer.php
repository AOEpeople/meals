<?php

namespace App\Mealz\MealBundle\Service\Mailer;

use Greew\OAuth2\Client\Provider\Azure;
use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class OAuthMailer extends PHPMailer
{
    public function __construct(
        private string $envHost,
        private int $envPort,
        private string $envAuthType,
        private string $envEmail,
        private string $envClientId,
        private string $envClientSecret,
        private string $envTenantId,
        private string $envRefreshToken,
    ) {
        date_default_timezone_set('Europe/Berlin');

        parent::__construct();

        $this->isSMTP();
        $this->Host = $this->envHost;
        $this->Port = $this->envPort;
        $this->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->SMTPAuth = true;
        $this->AuthType = $this->envAuthType;
        $this->SMTPDebug = SMTP::DEBUG_OFF;

        $this->setOAuth(
            new OAuth(
                [
                    'provider' => new Azure(
                        [
                            'clientId' => $this->envClientId,
                            'clientSecret' => $this->envClientSecret,
                            'tenantId' => $this->envTenantId,
                        ]
                    ),
                    'clientId' => $this->envClientId,
                    'clientSecret' => $this->envClientSecret,
                    'refreshToken' => $this->envRefreshToken,
                    'userName' => $this->envEmail,
                ]
            )
        );
    }
}
