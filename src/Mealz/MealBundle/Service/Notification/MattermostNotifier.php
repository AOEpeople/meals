<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Notification;

use App\Mealz\MealBundle\Service\Logger\MealsLoggerInterface;
use Exception;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Sends notifications to a Mattermost channel.
 */
class MattermostNotifier implements NotifierInterface
{
    private const HTTP_STATUS_SUCCESS = 200;

    private HttpClientInterface $httpClient;
    private MealsLoggerInterface $logger;

    /**
     * Flag to enable/disable notifications.
     */
    private bool $enabled;

    private string $webhookURL;

    private string $env;

    /**
     * User-friendly bot name that will be displayed in the notification message.
     */
    private string $username;

    private string $appName;

    public function __construct(
        HttpClientInterface $httpClient,
        MealsLoggerInterface $logger,
        bool $enabled,
        string $webhookURL,
        string $username,
        string $appName,
        string $env
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;

        $this->enabled = $enabled;
        $this->webhookURL = $webhookURL;
        $this->username = $username;
        $this->appName = $appName;
        $this->env = $env;
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
                'attachments' => [[
                    'author_name' => $this->env,
                    'text' => $message->getContent(),
                    'title' => $this->appName,
                ]],
            ],
        ];

        try {
            $response = $this->httpClient->request('POST', $this->webhookURL, $requestOptions);
            $responseStatus = $response->getStatusCode();
        } catch (TransportExceptionInterface|Exception $e) {
            $this->logger->logException($e, 'message send error');

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
