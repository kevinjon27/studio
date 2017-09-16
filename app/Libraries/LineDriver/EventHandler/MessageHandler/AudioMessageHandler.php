<?php
namespace App\Libraries\LineDriver\EventHandler\MessageHandler;

use Illuminate\Support\Facades\Log;
use LINE\LINEBot;
use LINE\LINEBot\Event\MessageEvent\AudioMessage;
use App\Libraries\LineDriver\EventHandler;
use App\Libraries\LineDriver\EventHandler\MessageHandler\Util\UrlBuilder;
use LINE\LINEBot\MessageBuilder\AudioMessageBuilder;

class AudioMessageHandler implements EventHandler
{
    /** @var LINEBot $bot */
    private $bot;


    private $logger;

    /** @var AudioMessage $audioMessage */
    private $audioMessage;

    public function __construct($bot, AudioMessage $audioMessage)
    {
        $this->bot = $bot;
        $this->logger = new Log();
        $this->audioMessage = $audioMessage;
    }

    public function handle()
    {
        $contentId = $this->audioMessage->getMessageId();
        $audio = $this->bot->getMessageContent($contentId)->getRawBody();

        $tmpfilePath = tempnam($_SERVER['DOCUMENT_ROOT'] . '/static/tmpdir', 'audio-');
        unlink($tmpfilePath);
        $filePath = $tmpfilePath . '.mp4';
        $filename = basename($filePath);

        $fh = fopen($filePath, 'x');
        fwrite($fh, $audio);
        fclose($fh);

        $replyToken = $this->audioMessage->getReplyToken();

        $url = UrlBuilder::buildUrl(['static', 'tmpdir', $filename]);

        $resp = $this->bot->replyMessage(
            $replyToken,
            new AudioMessageBuilder($url, 100)
        );

        $this->logger->info($resp->getRawBody());
    }
}
