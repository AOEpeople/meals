<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Mailer;

use Override;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class RestApiMailer implements MailerInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly string $webhookUrl,
        private readonly string $webhookToken,
        private readonly string $appName,
        private readonly string $appEnv,
    ) {
    }

    #[Override]
    public function send(string $recipient, string $subject, string $content, bool $isHTML = false): void
    {
        $payload = [
            'to' => $recipient,
            'subject' => $subject,
            'body' => $content,
            'isHtml' => $isHTML,
            'source' => [
                'app' => $this->appName,
                'env' => $this->appEnv,
            ],
        ];

        try {
            $response = $this->httpClient->request('POST', $this->webhookUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->webhookToken,
                ],
                'json' => $payload,
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode >= 300) {
                $this->logger->error('RestApiMailer: webhook returned unexpected status', [
                    'status_code' => $statusCode,
                    'recipient' => $recipient,
                    'subject' => $subject,
                ]);
            }
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('RestApiMailer: failed to reach webhook', [
                'exception' => $e->getMessage(),
                'recipient' => $recipient,
                'subject' => $subject,
            ]);
        }
    }
}
