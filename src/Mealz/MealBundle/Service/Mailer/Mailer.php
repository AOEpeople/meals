<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Mailer;

use Exception;
use Override;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Log\LoggerInterface;

final class Mailer implements MailerInterface
{
    private string $senderEmail;

    private LoggerInterface $logger;

    private OAuthMailer $mailer;

    public function __construct(
        OAuthMailer $mailer,
        LoggerInterface $logger,
        string $senderEmail
    ) {
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->senderEmail = $senderEmail;
    }

    #[Override]
    public function send(string $recipient, string $subject, string $content, bool $isHTML = false): void
    {
        try {
            $this->mailer->setFrom($this->senderEmail);
            $this->mailer->addAddress($recipient);

            $this->mailer->Subject = $subject;
            $this->mailer->CharSet = PHPMailer::CHARSET_UTF8;
            $this->mailer->Body = strip_tags($content);
            $this->mailer->msgHTML($content);
            $this->mailer->isHTML($isHTML);

            if (!$this->mailer->send()) {
                $this->logger->error('email send error: ' . $this->mailer->ErrorInfo);
            }
        } catch (Exception $e) {
            $this->logger->error('email send error', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
