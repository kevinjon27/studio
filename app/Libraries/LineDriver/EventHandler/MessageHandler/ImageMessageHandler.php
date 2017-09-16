<?php

namespace App\Libraries\LineDriver\EventHandler\MessageHandler;

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

        $tmpfilePath = tempnam(asset('line/images/'), 'image-');
        unlink($tmpfilePath);
        $filePath = $tmpfilePath . '.jpg';
        $filename = basename($filePath);
        $content = file_get_contents($image);
        file_put_contents($filePath, $content);

        $tempfile = tmpfile();
        $resp = fwrite($tempfile, $image->getRawBody());
        Log::info('temp : '.$tempfile);
        Log::info('image : '.$resp);

        $replyToken = $this->imageMessage->getReplyToken();

        $url = UrlBuilder::buildUrl(['public', 'tmpdir', $filename]);
        Log::info('URL: '.$url);

        // NOTE: You should pass the url of small image to `previewImageUrl`.
        // This sample doesn't treat that.
        $this->bot->replyMessage($replyToken, new ImageMessageBuilder($url, $url));
    }
}
