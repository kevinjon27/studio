<?php

namespace App\Libraries\LineDriver\EventHandler;

use LINE\LINEBot;
use LINE\LINEBot\Event\FollowEvent;
use App\Libraries\LineDriver\EventHandler;

class FollowEventHandler implements EventHandler
{
    /** @var LINEBot $bot */
    private $bot;

    /** @var FollowEvent $followEvent */
    private $followEvent;

    public function __construct($bot, FollowEvent $followEvent)
    {
        $this->bot = $bot;
        $this->followEvent = $followEvent;
    }

    public function handle()
    {
        $this->bot->replyText($this->followEvent->getReplyToken(), 'Got followed event');
    }
}
