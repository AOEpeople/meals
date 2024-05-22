<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Notification;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Sends notifications to a Mattermost channel.
 */
class MattermostNotifier implements NotifierInterface
{
    private const int HTTP_STATUS_SUCCESS = 200;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,

        // Flag to enable/disable notifications
        private readonly bool $enabled,
        private readonly string $webhookURL,

        // User-friendly bot name that will be displayed in the notification message
        private readonly string $username,
        private readonly string $appName
    ) {
    }

    /**
     * Sends a message to configured Mattermost channel.
     */
    public function send(MessageInterface $message): bool
    {
        if (false === $this->enabled) {
            $this->logger->debug('notifier disabled; message not sent');

            return false;
        }

        $requestOptions = [
            'json' => [
                'username' => $this->username,
                'attachments' => [
                    [
                        'text' => $message->getContent(),
                        'title' => $this->appName,
                    ],
                ],
            ],
        ];

        try {
            $response = $this->httpClient->request('POST', $this->webhookURL, $requestOptions);
            $responseStatus = $response->getStatusCode();
        } catch (TransportExceptionInterface|Exception $e) {
            $this->logger->error('message send error', ['trace' => $e->getTraceAsString()]);

            return false;
        }

        if (self::HTTP_STATUS_SUCCESS !== $responseStatus) {
            $this->logger->error('message sent failure', [
                'url' => $this->webhookURL,
                'response_status' => $responseStatus,
            ]);

            return false;
        }

        return true;
    }
}
