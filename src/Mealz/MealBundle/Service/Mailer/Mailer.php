<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Mailer;

use Exception;
use Override;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface as SymfonyMailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class Mailer implements MailerInterface
{
    private string $senderEmail;

    private string $mailerType;

    private LoggerInterface $logger;

    private OAuthMailer $oAuthMailer;

    private SymfonyMailerInterface $symfonyMailer;

    public function __construct(
        OAuthMailer $oAuthMailer,
        SymfonyMailerInterface $symfonyMailer,
        LoggerInterface $logger,
        string $senderEmail,
        string $mailerType
    ) {
        $this->oAuthMailer = $oAuthMailer;
        $this->symfonyMailer = $symfonyMailer;
        $this->logger = $logger;
        $this->senderEmail = $senderEmail;
        $this->mailerType = $mailerType;
    }

    #[Override]
    public function send(string $recipient, string $subject, string $content, bool $isHTML = false): void
    {
        if ('ms_oauth_smtp' === $this->mailerType) {
            try {
                $this->oAuthMailer->setFrom($this->senderEmail);
                $this->oAuthMailer->addAddress($recipient);

                $this->oAuthMailer->Subject = $subject;
                $this->oAuthMailer->CharSet = PHPMailer::CHARSET_UTF8;
                $this->oAuthMailer->Body = strip_tags($content);
                $this->oAuthMailer->msgHTML($content);
                $this->oAuthMailer->isHTML($isHTML);

                if (!$this->oAuthMailer->send()) {
                    $this->logger->error('email send error: ' . $this->oAuthMailer->ErrorInfo);
                }
            } catch (Exception $e) {
                $this->logger->error('email send error', [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        } else {
            $email = (new Email())
                ->from(Address::create($this->senderEmail))
                ->to($recipient)
                ->subject($subject)
                ->text(strip_tags($content))
                ->html($content);

            if ($isHTML) {
                $email->html($content);
            }

            try {
                $this->symfonyMailer->send($email);
            } catch (TransportExceptionInterface $e) {
                $this->logger->error('email send error', [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
    }
}
