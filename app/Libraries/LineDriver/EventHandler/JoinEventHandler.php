<?php

namespace App\Libraries\LineDriver\EventHandler;

use App\Libraries\LineDriver\Exceptions\LineException;
use LINE\LINEBot;
use LINE\LINEBot\Event\JoinEvent;
use App\Libraries\LineDriver\EventHandler;

class JoinEventHandler implements EventHandler
{
    /** @var LINEBot $bot */
    private $bot;

    /** @var JoinEvent $joinEvent */
    private $joinEvent;

    public function __construct($bot, JoinEvent $joinEvent)
    {
        $this->bot = $bot;
        $this->joinEvent = $joinEvent;
    }

    public function handle()
    {
        if ($this->joinEvent->isGroupEvent()) {
            $id = $this->joinEvent->getGroupId();
        } elseif ($this->joinEvent->isRoomEvent()) {
            $id = $this->joinEvent->getRoomId();
        } else {
            throw new LineException('Unknown event type');
        }

        $this->bot->replyText(
            $this->joinEvent->getReplyToken(),
            sprintf('Joined %s %s', $this->joinEvent->getType(), $id)
        );
    }
}
