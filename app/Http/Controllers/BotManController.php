<?php

namespace App\Http\Controllers;

use App\Libraries\LineDriver\LineDriver;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use Illuminate\Http\Request;
use App\Conversations\ExampleConversation;
use Illuminate\Support\Facades\Log;

class BotManController extends Controller
{
    /**
     * Place your BotMan logic here.
     */
    public function handle(Request $request)
    {
        Log::info('header: '. response()->json($request->headers->all()));
        Log::info('result: '. $request->all());

        DriverManager::loadDriver(LineDriver::class);

        $config = config('botman');
        // create an instance
        $botman = BotManFactory::create($config);

        $botman->hears('Hi', function ($bot) {
            $bot->reply('Hello!');
        });

        $botman->listen();
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function tinker()
    {
        return view('tinker');
    }

    /**
     * Loaded through routes/botman.php
     * @param  BotMan $bot
     */
    public function startConversation(BotMan $bot)
    {
        $bot->startConversation(new ExampleConversation());
    }
}
