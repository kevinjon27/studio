<?php
namespace App\Libraries\LineDriver\EventHandler\MessageHandler;

use Illuminate\Support\Facades\Log;
use LINE\LINEBot;
use LINE\LINEBot\Event\MessageEvent\AudioMessage;
use LINE\LINEBot\KitchenSink\EventHandler;
use App\Libraries\LineDriver\EventHandler\MessageHandler\Util\UrlBuilder;
use LINE\LINEBot\MessageBuilder\AudioMessageBuilder;
use Symfony\Component\HttpFoundation\Request;

class AudioMessageHandler implements EventHandler
{
    /** @var LINEBot $bot */
    private $bot;

    private $request;

    private $logger;

    /** @var AudioMessage $audioMessage */
    private $audioMessage;

    public function __construct($bot, Request $request, AudioMessage $audioMessage)
    {
        $this->bot = $bot;
        $this->logger = new Log();
        $this->request = $request;
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

        $url = UrlBuilder::buildUrl($this->request, ['static', 'tmpdir', $filename]);

        $resp = $this->bot->replyMessage(
            $replyToken,
            new AudioMessageBuilder($url, 100)
        );

        $this->logger->info($resp->getRawBody());
    }
}
