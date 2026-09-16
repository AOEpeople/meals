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
        private readonly int $timeoutSeconds = 15,
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
            $result = $this->sendCurlRequest($payload);
        
            if ($result['statusCode'] < 200 || $result['statusCode'] >= 300) {
                $this->logger->error('RestApiMailer: webhook returned unexpected status', [
                    'status_code' => $result['statusCode'],
                    'recipient'   => $recipient,
                    'subject'     => $subject,
                ]);
            }
        } catch (\RuntimeException $e) {
            $this->logger->error('RestApiMailer: failed to reach webhook', [
                'exception' => $e->getMessage(),
                'recipient' => $recipient,
                'subject'   => $subject,
            ]);
        }
    }

    private function sendCurlRequest(array $payload): array
    {
        $ch = curl_init($this->webhookUrl);
 
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_THROW_ON_ERROR),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->webhookToken,
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeoutSeconds,
 
            // enforce TLS 1.2 ---
            CURLOPT_SSLVERSION     => CURL_SSLVERSION_MAX_TLSv1_2,
        ]);
 
        $body     = curl_exec($ch);
        $errno    = curl_errno($ch);
        $error    = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
 
        if ($errno !== 0) {
            throw new \RuntimeException(
                sprintf('n8n-Webhook-Aufruf fehlgeschlagen (curl errno %d): %s', $errno, $error)
            );
        }
 
        return [
            'statusCode' => $httpCode,
            'body'       => (string) $body,
        ];
    }
}
