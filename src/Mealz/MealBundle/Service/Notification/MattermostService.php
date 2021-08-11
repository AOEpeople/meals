<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Notification;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Sends notifications to a Mattermost channel.
 */
class MattermostService implements NotifierInterface
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;

    /**
     * Flag to enable/disable notifications.
     */
    private bool $enabled;

    private string $webhookURL;

    /**
     * User friendly bot name that will be displayed in the notification message.
     */
    private string $username;

    /**
     * TODO: Where is it displayed?? Remove if not needed.
     */
    private string $appName;

    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $logger,
        bool $enabled,
        string $webhookURL,
        string $username,
        string $appName

    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;

        $this->enabled = $enabled;
        $this->webhookURL = $webhookURL;
        $this->username = $username;
        $this->appName = $appName;
    }

    /**
     * Sends a message to configured Mattermost channel.
     *
     */
    public function sendAlert(string $message): ?ResponseInterface
    {
        if (false === $this->enabled) {
            return null;
        }

        try {
            $response = $this->httpClient->request(
                'POST',
                $this->webhookURL,
                [
                    'json' => [
                        'username' => $this->username,
                        'attachments' => [[
                            'text' => $message,
                            'title' => $this->appName,
                        ]],
                    ],
                ]
            );
        } catch (TransportExceptionInterface $e) {
            $this->logger->warning(
                $e->getMessage(),
                [
                    'trace' => $e->getTraceAsString(),
                    'msg' => $message,
                ]
            );

            return null;
        }

        return $response;
    }
}
