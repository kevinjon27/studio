<?php
    /**
     * Created by kevin jonathan<kevinjonathan2701@gmail.com> on 15/09/17.
     */

    namespace App\Libraries\LineDriver;

use BotMan\BotMan\Messages\Incoming\Answer;
use LINE\LINEBot\Exception\InvalidEventRequestException;
use LINE\LINEBot\Exception\InvalidSignatureException;

    use BotMan\BotMan\Drivers\HttpDriver;
    use BotMan\BotMan\Interfaces\DriverInterface;
    use BotMan\BotMan\Interfaces\UserInterface;
    use BotMan\BotMan\Messages\Incoming\IncomingMessage;
    use Illuminate\Support\Collection;
    use LINE\LINEBot;
    use LINE\LINEBot\Constant\HTTPHeader;
    use Symfony\Component\HttpFoundation\ParameterBag;
    use Symfony\Component\HttpFoundation\Request;
    use LINE\LINEBot\HTTPClient\CurlHTTPClient;
    use Symfony\Component\HttpFoundation\Response;

    class LineDriver extends HttpDriver {

        const DRIVER_NAME = 'Line';

        /** @var string */
        protected $signature;

        /** @var LINEBot */
        protected $line;

        protected $messages = [];

        protected $matchesRequest = false;

        /**
         * @param Request $request
         */
        public function buildPayload(Request $request)
        {
            $this->payload = new ParameterBag((array) json_decode($request->getContent(), true));
            $this->signature = $request->headers->get(HTTPHeader::LINE_SIGNATURE);
            $this->config = Collection::make($this->config->get('line'));
            $this->line = new LINEBot(new CurlHTTPClient($this->config->get('channel_access_token')),[
                'channelSecret' => $this->config->get('channel_secret')
            ]);

            try {
                $this->event = Collection::make($this->line->parseEventRequest($request->getContent(), $this->signature));
                $this->matchesRequest = true;
            } catch (InvalidSignatureException $e) {
                $this->matchesRequest = false;
            } catch (InvalidEventRequestException $e) {
                $this->matchesRequest = false;
            }

        }

        /**
         * Determine if the request is for this driver.
         *
         * @return bool
         */
        public function matchesRequest()
        {
            return $this->matchesRequest;
        }

        public function getMessages()
        {
            if (empty($this->messages)) {
                foreach ($this->event as $event) {
                    $this->messages[] = new IncomingMessage($event->getText(), $event->getUserId(), $event->getReplyToken());
                }
            }
            return $this->messages;
        }

        public function isConfigured()
        {
            return true;
        }

        public function getUser(IncomingMessage $matchingMessage)
        {
            // TODO: Implement getUser() method.
        }

        public function getConversationAnswer(IncomingMessage $message)
        {
            return Answer::create($message->getText())->setMessage($message);
        }

        public function buildServicePayload($message, $matchingMessage, $additionalParameters = [])
        {
            return [
                'replyToken' => $matchingMessage->getSender(),
                'message' => $message->getText()
            ];
        }

        public function sendPayload($payload)
        {
            return $this->line->replyText($payload['replyToken'], $payload['message']);
        }

        public function sendRequest($endpoint, array $parameters, IncomingMessage $matchingMessage)
        {
            // TODO: Implement sendRequest() method.
        }

    }
