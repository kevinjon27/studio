<?php
    /**
     * Created by kevin jonathan<kevinjonathan2701@gmail.com> on 15/09/17.
     */

    namespace App\Libraries\LineDriver;

    use App\Libraries\LineDriver\Extensions\User;
    use BotMan\BotMan\Messages\Incoming\Answer;
    use Illuminate\Support\Facades\Log;
    use LINE\LINEBot\Event\BeaconDetectionEvent;
    use LINE\LINEBot\Event\FollowEvent;
    use LINE\LINEBot\Event\JoinEvent;
    use LINE\LINEBot\Event\LeaveEvent;
    use LINE\LINEBot\Event\MessageEvent;
    use LINE\LINEBot\Event\MessageEvent\AudioMessage;
    use LINE\LINEBot\Event\MessageEvent\ImageMessage;
    use LINE\LINEBot\Event\MessageEvent\LocationMessage;
    use LINE\LINEBot\Event\MessageEvent\StickerMessage;
    use LINE\LINEBot\Event\MessageEvent\TextMessage;
    use LINE\LINEBot\Event\MessageEvent\UnknownMessage;
    use LINE\LINEBot\Event\MessageEvent\VideoMessage;
    use LINE\LINEBot\Event\PostbackEvent;
    use LINE\LINEBot\Event\UnfollowEvent;
    use LINE\LINEBot\Event\UnknownEvent;
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

        protected $log;

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
            $this->log = new Log();

            try {
                $this->event = Collection::make($this->line->parseEventRequest($request->getContent(), $this->signature));
                $this->matchesRequest = true;
            } catch (InvalidSignatureException $e) {
                $this->matchesRequest = false;
                $this->log->error('Invalid signature: ', $e->getMessage());
            } catch (InvalidEventRequestException $e) {
                $this->matchesRequest = false;
                $this->log->error('Invalid event request:', $e->getMessage());
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
                    if ($event instanceof MessageEvent) {
                        if ($event instanceof TextMessage) {
                            $this->messages[] = new IncomingMessage($event->getText(), $event->getUserId(), $event->getReplyToken());
                        } elseif ($event instanceof StickerMessage) {

                        } elseif ($event instanceof LocationMessage) {

                        } elseif ($event instanceof ImageMessage) {

                        } elseif ($event instanceof AudioMessage) {

                        } elseif ($event instanceof VideoMessage) {

                        } elseif ($event instanceof UnknownMessage) {
                            $this->log->info(sprintf(
                                              'Unknown message type has come [message type: %s]',
                                              $event->getMessageType()
                                          ));
                        } else {
                            // Unexpected behavior (just in case)
                            // something wrong if reach here
                            $this->log->info(sprintf(
                                              'Unexpected message type has come, something wrong [class name: %s]',
                                              get_class($event)
                                          ));
                            continue;
                        }
                    } elseif ($event instanceof UnfollowEvent) {

                    } elseif ($event instanceof FollowEvent) {

                    } elseif ($event instanceof JoinEvent) {

                    } elseif ($event instanceof LeaveEvent) {

                    } elseif ($event instanceof PostbackEvent) {

                    } elseif ($event instanceof BeaconDetectionEvent) {

                    } elseif ($event instanceof UnknownEvent) {
                        $this->log->info(sprintf('Unknown message type has come [type: %s]', $event->getType()));
                    } else {
                        // Unexpected behavior (just in case)
                        // something wrong if reach here
                        $this->log->info(sprintf(
                                          'Unexpected event type has come, something wrong [class name: %s]',
                                          get_class($event)
                                      ));
                        continue;
                    }
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
            $user =  $this->line->getProfile($matchingMessage->getSender());
            $profile = Collection::make($user->getJSONDecodedBody());

            $this->log->info('User profile: ',$user->getJSONDecodedBody());

            $user_id = $profile->get('userId') ? $profile->get('userId') : null;
            $display_name = $profile->get('displayName') ? $profile->get('displayName') : null;

            $profile = [
                'picture_url' => $profile->get('pictureUrl') ? $profile->get('pictureUrl') : null,
                'status_message' => $profile->get('statusMessage') ? $profile->get('statusMessage') :null
            ];

            return new User($matchingMessage->getSender(), $display_name, null, $user_id, $profile);
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
