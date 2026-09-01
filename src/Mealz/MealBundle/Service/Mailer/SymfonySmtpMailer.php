<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Mailer;

use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface as SymfonyMailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class SymfonySmtpMailer implements MailerInterface
{
    public function __construct(
        private readonly SymfonyMailerInterface $symfonyMailer,
        private readonly LoggerInterface $logger,
        private readonly string $senderEmail,
    ) {}

    #[Override]
    public function send(string $recipient, string $subject, string $content, bool $isHTML = false): void
    {
        $email = new Email()
            ->from(Address::create($this->senderEmail))
            ->to($recipient)
            ->subject($subject)
            ->text(strip_tags($content));

        if ($isHTML) {
            $email->html($content);
        }

        try {
            $this->symfonyMailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('SymfonySmtpMailer: email send error', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
