<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Mailer;

use Greew\OAuth2\Client\Provider\Azure;
use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class OAuthMailer extends PHPMailer
{
    public function __construct(
        string $envHost,
        int $envPort,
        string $envAuthType,
        string $envEmail,
        string $envClientId,
        string $envClientSecret,
        string $envTenantId,
        string $envRefreshToken
    ) {
        date_default_timezone_set('Europe/Berlin');

        parent::__construct();

        $this->isSMTP();
        $this->Host = $envHost;
        $this->Port = $envPort;
        $this->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->SMTPAuth = true;
        $this->AuthType = $envAuthType;
        $this->SMTPDebug = SMTP::DEBUG_OFF;

        $this->setOAuth(
            new OAuth(
                [
                    'provider' => new Azure(
                        [
                            'clientId' => $envClientId,
                            'clientSecret' => $envClientSecret,
                            'tenantId' => $envTenantId,
                        ]
                    ),
                    'clientId' => $envClientId,
                    'clientSecret' => $envClientSecret,
                    'refreshToken' => $envRefreshToken,
                    'userName' => $envEmail,
                ]
            )
        );
    }
}
