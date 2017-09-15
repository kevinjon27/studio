<?php

namespace App\Libraries\LineDriver\EventHandler;

use LINE\LINEBot;
use LINE\LINEBot\Event\PostbackEvent;
use App\Libraries\LineDriver\EventHandler;

class PostbackEventHandler implements EventHandler
{
    /** @var LINEBot $bot */
    private $bot;

    /** @var PostbackEvent $postbackEvent */
    private $postbackEvent;

    public function __construct($bot, PostbackEvent $postbackEvent)
    {
        $this->bot = $bot;
        $this->postbackEvent = $postbackEvent;
    }

    public function handle()
    {
        $this->bot->replyText(
            $this->postbackEvent->getReplyToken(),
            'Got postback ' . $this->postbackEvent->getPostbackData()
        );
    }
}
