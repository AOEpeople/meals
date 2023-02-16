<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Command;

use App\Mealz\MealBundle\Event\KeepAliveConnectionEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class KeepAliveConnectionCommand extends Command
{
    private $eventDispatcher;

    protected static $defaultName = 'meals:keep-alive-connection';

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct();
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->eventDispatcher->dispatch(new KeepAliveConnectionEvent());

        return 0;
    }
}
