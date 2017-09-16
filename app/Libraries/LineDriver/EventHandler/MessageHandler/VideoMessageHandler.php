<?php

namespace App\Libraries\LineDriver\EventHandler\MessageHandler;

use LINE\LINEBot;
use LINE\LINEBot\Event\MessageEvent\VideoMessage;
use App\Libraries\LineDriver\EventHandler;
use App\Libraries\LineDriver\EventHandler\MessageHandler\Util\UrlBuilder;
use LINE\LINEBot\MessageBuilder\VideoMessageBuilder;

class VideoMessageHandler implements EventHandler
{
    /** @var LINEBot $bot */
    private $bot;

    /** @var VideoMessage $videoMessage */
    private $videoMessage;

    public function __construct($bot, VideoMessage $videoMessage)
    {
        $this->bot = $bot;
        $this->request = $request;
        $this->videoMessage = $videoMessage;
    }

    public function handle()
    {
        $contentId = $this->videoMessage->getMessageId();
        $video = $this->bot->getMessageContent($contentId)->getRawBody();

        $tmpfilePath = tempnam($_SERVER['DOCUMENT_ROOT'] . '/static/tmpdir', 'video-');
        unlink($tmpfilePath);
        $filePath = $tmpfilePath . '.mp4';
        $filename = basename($filePath);

        $fh = fopen($filePath, 'x');
        fwrite($fh, $video);
        fclose($fh);

        $replyToken = $this->videoMessage->getReplyToken();

        $url = UrlBuilder::buildUrl(['static', 'tmpdir', $filename]);

        // NOTE: You should pass the url of thumbnail image to `previewImageUrl`.
        // This sample doesn't treat that so this sample cannot show the thumbnail.
        $this->bot->replyMessage($replyToken, new VideoMessageBuilder($url, $url));
    }
}
