<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Mailer;

interface MailerInterface
{
    /**
     * Sends an email to given recipient.
     *
     * @param string $recipient Recipient email
     * @param string $subject   Email subject
     * @param string $content   Email content
     * @param bool   $isHTML    Flag to specify that the email content is HTML
     */
    public function send(string $recipient, string $subject, string $content, bool $isHTML = false): void;
}
