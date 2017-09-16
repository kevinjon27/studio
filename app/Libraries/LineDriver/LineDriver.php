<?php
    /**
     * Created by kevin jonathan<kevinjonathan2701@gmail.com> on 15/09/17.
     */

    namespace App\Libraries\LineDriver;

    use App\Libraries\LineDriver\EventHandler\BeaconEventHandler;
    use App\Libraries\LineDriver\EventHandler\FollowEventHandler;
    use App\Libraries\LineDriver\EventHandler\JoinEventHandler;
    use App\Libraries\LineDriver\EventHandler\LeaveEventHandler;
    use App\Libraries\LineDriver\EventHandler\MessageHandler\AudioMessageHandler;
    use App\Libraries\LineDriver\EventHandler\MessageHandler\ImageMessageHandler;
    use App\Libraries\LineDriver\EventHandler\MessageHandler\LocationMessageHandler;
    use App\Libraries\LineDriver\EventHandler\MessageHandler\StickerMessageHandler;
    use App\Libraries\LineDriver\EventHandler\MessageHandler\VideoMessageHandler;
    use App\Libraries\LineDriver\EventHandler\PostbackEventHandler;
    use App\Libraries\LineDriver\EventHandler\UnfollowEventHandler;
    use App\Libraries\LineDriver\Exceptions\LineException;
    use BotMan\BotMan\Messages\Attachments\Audio;
    use BotMan\BotMan\Messages\Attachments\Image;
    use BotMan\BotMan\Messages\Attachments\Location;
    use BotMan\BotMan\Messages\Attachments\Video;
    use BotMan\BotMan\Messages\Incoming\Answer;
    use BotMan\BotMan\Users\User;
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

    class LineDriver extends HttpDriver {

        const DRIVER_NAME = 'Line';

        /** @var string */
        protected $signature;

        /** @var LINEBot */
        protected $line;

        protected $messages = [];

        protected $matchesRequest = false;

        private $supportedAttachments = [
            Video::class,
            Audio::class,
            Image::class,
            Location::class
        ];

        protected $request;

        /**
         * @param Request $request
         */
        public function buildPayload(Request $request)
        {
            $this->request = $request->request->all();
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
                throw new LineException('Invalid signature: '. $e->getMessage(), 0, $e);
            } catch (InvalidEventRequestException $e) {
                $this->matchesRequest = false;
                throw new LineException('Invalid event request: '. $e->getMessage(), 0, $e);
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
            $handle = null;
            if (empty($this->messages)) {
                foreach ($this->event as $event) {
                    if ($event instanceof MessageEvent) {
                        if ($event instanceof TextMessage) {
                            $this->messages[] = new IncomingMessage($event->getText(), $event->getUserId(), $event->getReplyToken());
                        } elseif ($event instanceof StickerMessage) {
                            $handle = new StickerMessageHandler($this->line, $event);
                        } elseif ($event instanceof LocationMessage) {
                            $handle = new LocationMessageHandler($this->line, $event);
                        } elseif ($event instanceof ImageMessage) {
                            $handle = new ImageMessageHandler($this->line, $this->request, $event);
                        } elseif ($event instanceof AudioMessage) {
                            $handle = new AudioMessageHandler($this->line, $this->request, $event);
                        } elseif ($event instanceof VideoMessage) {
                            $hande = new VideoMessageHandler($this->line, $this->request, $event);
                        } elseif ($event instanceof UnknownMessage) {
                            throw new LineException(sprintf(
                                                        'Unknown message type has come [message type: %s]',
                                                        $event->getMessageType()
                                                    ));
                        } else {
                            throw new LineException(sprintf(
                                                        'Unexpected message type has come, something wrong [class name: %s]',
                                                        get_class($event)
                                                    ));
                        }
                    } elseif ($event instanceof UnfollowEvent) {
                        $handler = new UnfollowEventHandler($this->line, $event);
                    } elseif ($event instanceof FollowEvent) {
                        $handler = new FollowEventHandler($this->line, $event);
                    } elseif ($event instanceof JoinEvent) {
                        $handler = new JoinEventHandler($this->line, $event);
                    } elseif ($event instanceof LeaveEvent) {
                        $handler = new LeaveEventHandler($this->line, $event);
                    } elseif ($event instanceof PostbackEvent) {
                        $handler = new PostbackEventHandler($this->line, $event);
                    } elseif ($event instanceof BeaconDetectionEvent) {
                        $handler = new BeaconEventHandler($this->line, $event);
                    } elseif ($event instanceof UnknownEvent) {
                        throw new LineException(sprintf('Unknown message type has come [type: %s]', $event->getType()));
                    } else {
                        throw new LineException(sprintf(
                                                    'Unexpected event type has come, something wrong [class name: %s]',
                                                    get_class($event)
                                                ));
                    }
                }
            }
            $handle->handle();
            return $this->messages;
        }

        public function isConfigured()
        {
            return true;
        }

        public function getUser(IncomingMessage $matchingMessage)
        {
            $user =  $this->line->getProfile($matchingMessage->getSender());

            if (!$user->isSucceeded()) {
                throw new LineException('Error retrieving user info');
            }

            $profile = Collection::make($user->getJSONDecodedBody());

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
