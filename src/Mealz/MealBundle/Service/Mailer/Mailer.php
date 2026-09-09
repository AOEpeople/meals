<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Mailer;

use InvalidArgumentException;
use Override;

final class Mailer implements MailerInterface
{
    private MailerInterface $activeMailer;

    public function __construct(
        private readonly string $mailerType,
        ?OAuthSMTPMailer $oAuthMailer = null,
        ?SymfonySmtpMailer $symfonyMailer = null,
        ?RestApiMailer $restApiMailer = null,
    ) {
        $this->activeMailer = match ($this->mailerType) {
            'ms_oauth_smtp' => $oAuthMailer ?? throw new InvalidArgumentException('OAuthSMTPMailer required for ms_oauth_smtp'),
            'rest_api_mailer' => $restApiMailer ?? throw new InvalidArgumentException('RestApiMailer required for rest_api_mailer'),
            default => $symfonyMailer ?? throw new InvalidArgumentException('SymfonySmtpMailer required as default'),
        };
    }

    #[Override]
    public function send(string $recipient, string $subject, string $content, bool $isHTML = false): void
    {
        $this->activeMailer->send($recipient, $subject, $content, $isHTML);
    }
}
