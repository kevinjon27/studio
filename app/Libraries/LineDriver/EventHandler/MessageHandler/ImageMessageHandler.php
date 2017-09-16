<?php

namespace App\Libraries\LineDriver\EventHandler\MessageHandler;

use Illuminate\Http\File;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot;
use LINE\LINEBot\Event\MessageEvent\ImageMessage;
use App\Libraries\LineDriver\EventHandler;
use App\Libraries\LineDriver\EventHandler\MessageHandler\Util\UrlBuilder;

use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;

class ImageMessageHandler implements EventHandler
{
    /** @var LINEBot $bot */
    private $bot;

    private $request;

    /** @var ImageMessage $imageMessage */
    private $imageMessage;

    public function __construct($bot, ImageMessage $imageMessage)
    {
        $this->bot = $bot;
        $this->imageMessage = $imageMessage;
    }

    public function handle()
    {
        $contentId = $this->imageMessage->getMessageId();
        $image = $this->bot->getMessageContent($contentId)->getRawBody();

        $tmpfilePath = tempnam($_SERVER['DOCUMENT_ROOT'] . '/public/tmpdir', 'image-');
        unlink($tmpfilePath);
        $filePath = $tmpfilePath . '.jpg';
        $filename = basename($filePath);

        $fh = fopen($filePath, 'x');
        fwrite($fh, $image);
        fclose($fh);
        rename($filePath, public_path('images/line/'.$filename));

        $replyToken = $this->imageMessage->getReplyToken();

        $url = asset('images/line/'.$filename);
        Log::info('url:'.$url);

        // NOTE: You should pass the url of small image to `previewImageUrl`.
        // This sample doesn't treat that.
        $resp = $this->bot->replyMessage($replyToken, new ImageMessageBuilder($url, $url));
        Log::info('resp:'.$resp);
    }
}
