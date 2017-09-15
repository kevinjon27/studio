<?php
    /**
     * Created by kevin jonathan<kevinjonathan2701@gmail.com> on 15/09/17.
     */

    namespace App\Libraries\LineDriver;

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

        /**
         * @param Request $request
         */
        public function buildPayload(Request $request)
        {
            $this->payload = new ParameterBag((array) json_decode($request->getContent(), true));
            $this->signature = $request->headers->get(HTTPHeader::LINE_SIGNATURE);
            $this->config = Collection::make($this->config->get('line'));
            $line = new LINEBot(new CurlHTTPClient($this->config->get('channel_access_token')),[
                'channelSecret' => $this->config->get('channel_secret')
            ]);

            /*
             * Uncomment this if you do on server. I comment the code because I do on my local.
             * */
            //$this->event = Collection::make($line->parseEventRequest($request->getContent(), $this->signature));
            $this->event = Collection::make($this->payload->get('events'));
        }

        /**
         * Determine if the request is for this driver.
         *
         * @return bool
         */

        public function matchesRequest()
        {
            // TODO: Implement matchesRequest() method.
        }

        public function getMessages()
        {
            // TODO: Implement getMessages() method.
        }

        public function isConfigured()
        {
            // TODO: Implement isConfigured() method.
        }

        public function getUser(IncomingMessage $matchingMessage)
        {
            // TODO: Implement getUser() method.
        }

        public function getConversationAnswer(IncomingMessage $message)
        {
            // TODO: Implement getConversationAnswer() method.
        }

        public function buildServicePayload($message, $matchingMessage, $additionalParameters = [])
        {
            // TODO: Implement buildServicePayload() method.
        }

        public function sendPayload($payload)
        {
            // TODO: Implement sendPayload() method.
        }

        public function sendRequest($endpoint, array $parameters, IncomingMessage $matchingMessage)
        {
            // TODO: Implement sendRequest() method.
        }

    }

