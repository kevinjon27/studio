<?php

namespace App\Libraries\LineDriver\EventHandler\MessageHandler;

use LINE\LINEBot;
use LINE\LINEBot\Event\MessageEvent\StickerMessage;
use LINE\LINEBot\KitchenSink\EventHandler;
use LINE\LINEBot\MessageBuilder\StickerMessageBuilder;

class StickerMessageHandler implements EventHandler
{
    /** @var LINEBot $bot */
    private $bot;

    /** @var StickerMessage $stickerMessage */
    private $stickerMessage;

    public function __construct($bot, StickerMessage $stickerMessage)
    {
        $this->bot = $bot;
        $this->stickerMessage = $stickerMessage;
    }

    public function handle()
    {
        $replyToken = $this->stickerMessage->getReplyToken();
        $packageId = $this->stickerMessage->getPackageId();
        $stickerId = $this->stickerMessage->getStickerId();
        $this->bot->replyMessage($replyToken, new StickerMessageBuilder($packageId, $stickerId));
    }
}
