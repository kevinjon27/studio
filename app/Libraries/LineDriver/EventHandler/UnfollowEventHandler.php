<?php

namespace App\Libraries\LineDriver\EventHandler;

use Illuminate\Support\Facades\Log;
use LINE\LINEBot;
use LINE\LINEBot\Event\UnfollowEvent;
use App\Libraries\LineDriver\EventHandler;

class UnfollowEventHandler implements EventHandler
{
    /** @var LINEBot $bot */
    private $bot;

    /** @var UnfollowEvent $unfollowEvent */
    private $unfollowEvent;

    private $logger;

    public function __construct($bot, UnfollowEvent $unfollowEvent)
    {
        $this->bot = $bot;
        $this->logger = new Log();
        $this->unfollowEvent = $unfollowEvent;
    }

    public function handle()
    {
        $this->logger->info(sprintf(
            'Unfollowed this bot %s %s',
            $this->unfollowEvent->getType(),
            $this->unfollowEvent->getUserId()
        ));
    }
}
