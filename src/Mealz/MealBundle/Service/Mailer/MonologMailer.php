<?php

namespace App\Mealz\MealBundle\Service\Mailer;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Monolog\LogRecord;
use Override;

class MonologMailer extends AbstractProcessingHandler
{
    private Mailer $mailer;

    protected ?FormatterInterface $formatter;
    private string $toEmail;
    private string $subject;

    public function __construct(
        Mailer $mailer,
        string $toEmail,
        string $subject = 'Meals Logged Error',
        $level = Logger::DEBUG,
        bool $bubble = true
    ) {
        parent::__construct($level, $bubble);
        $this->mailer = $mailer;
        $this->toEmail = $toEmail;
        $this->subject = $subject;
        $this->formatter = new HtmlFormatter();
    }

    #[Override]
    public function handleBatch(array $records): void
    {
        if (empty($records)) {
            return;
        }

        $messages = '';
        foreach ($records as $record) {
            $messages .= $this->formatter->format($record);
            $messages .= "\n\n";
        }

        $this->mailer
            ->send($this->toEmail, $this->subject, $messages);
    }

    #[Override]
    protected function write(array|LogRecord $record): void
    {
        $this->mailer
            ->send(
                $this->toEmail,
                $this->subject,
                $this->formatter->format($record)
            );
    }
}
