<?php

namespace App\Libraries\LineDriver\EventHandler\MessageHandler;

use LINE\LINEBot;
use LINE\LINEBot\Event\MessageEvent\LocationMessage;
use App\Libraries\LineDriver\EventHandler;
use LINE\LINEBot\MessageBuilder\LocationMessageBuilder;

class LocationMessageHandler implements EventHandler
{
    /** @var LINEBot $bot */
    private $bot;

    /** @var LocationMessage $event */
    private $locationMessage;

    public function __construct($bot, LocationMessage $locationMessage)
    {
        $this->bot = $bot;
        $this->locationMessage = $locationMessage;
    }

    public function handle()
    {
        $replyToken = $this->locationMessage->getReplyToken();
        $title = $this->locationMessage->getTitle();
        $address = $this->locationMessage->getAddress();
        $latitude = $this->locationMessage->getLatitude();
        $longitude = $this->locationMessage->getLongitude();

        $this->bot->replyMessage(
            $replyToken,
            new LocationMessageBuilder($title, $address, $latitude, $longitude)
        );
    }
}
