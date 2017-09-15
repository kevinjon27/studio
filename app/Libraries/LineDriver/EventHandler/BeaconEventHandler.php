<?php

namespace App\Libraries\LineDriver\EventHandler;

use LINE\LINEBot;
use LINE\LINEBot\Event\BeaconDetectionEvent;
use LINE\LINEBot\KitchenSink\EventHandler;

class BeaconEventHandler implements EventHandler
{
    /** @var LINEBot $bot */
    private $bot;

    /* @var BeaconDetectionEvent $beaconEvent */
    private $beaconEvent;

    public function __construct($bot, BeaconDetectionEvent $beaconEvent)
    {
        $this->bot = $bot;
        $this->beaconEvent = $beaconEvent;
    }

    public function handle()
    {
        $this->bot->replyText(
            $this->beaconEvent->getReplyToken(),
            'Got beacon message ' . $this->beaconEvent->getHwid()
        );
    }
}
