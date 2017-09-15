<?php

namespace App\Libraries\LineDriver\EventHandler;

use App\Libraries\LineDriver\Exceptions\LineException;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot;
use LINE\LINEBot\Event\LeaveEvent;
use LINE\LINEBot\KitchenSink\EventHandler;

class LeaveEventHandler implements EventHandler
{
    /** @var LINEBot $bot */
    private $bot;

    private $logger;
    /** @var LeaveEvent $leaveEvent */
    private $leaveEvent;

    public function __construct($bot, LeaveEvent $leaveEvent)
    {
        $this->bot = $bot;
        $this->logger = new Log();
        $this->leaveEvent = $leaveEvent;
    }

    public function handle()
    {
        if ($this->leaveEvent->isGroupEvent()) {
            $id = $this->leaveEvent->getGroupId();
        } elseif ($this->leaveEvent->isRoomEvent()) {
            $id = $this->leaveEvent->getRoomId();
        } else {
            throw new LineException('Unknown event type');
        }

        $this->logger->info(sprintf('Left %s %s', $this->leaveEvent->getType(), $id));
    }
}
