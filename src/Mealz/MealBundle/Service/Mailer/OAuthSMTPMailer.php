<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Mailer;

use Exception;
use Override;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Log\LoggerInterface;

final class OAuthSMTPMailer implements MailerInterface
{
    public function __construct(
        private readonly OAuthMailer $oAuthMailer,
        private readonly LoggerInterface $logger,
        private readonly string $senderEmail,
    ) {
    }

    #[Override]
    public function send(string $recipient, string $subject, string $content, bool $isHTML = false): void
    {
        try {
            $this->oAuthMailer->setFrom($this->senderEmail);
            $this->oAuthMailer->addAddress($recipient);

            $this->oAuthMailer->Subject = $subject;
            $this->oAuthMailer->CharSet = PHPMailer::CHARSET_UTF8;
            $this->oAuthMailer->Body = strip_tags($content);
            $this->oAuthMailer->msgHTML($content);
            $this->oAuthMailer->isHTML($isHTML);

            if (!$this->oAuthMailer->send()) {
                $this->logger->error('OAuthSMTPMailer: email send error: ' . $this->oAuthMailer->ErrorInfo);
            }
        } catch (Exception $e) {
            $this->logger->error('OAuthSMTPMailer: email send error', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
