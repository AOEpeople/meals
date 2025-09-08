<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Mailer;

use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface as SymfonyMailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class Mailer implements MailerInterface
{
    private string $senderEmail;

    private LoggerInterface $logger;
    private SymfonyMailerInterface $mailer;

    public function __construct(
        SymfonyMailerInterface $mailer,
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
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('email send error', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
