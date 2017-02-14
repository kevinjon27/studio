<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Mpociot\BotMan\BotMan;
use Illuminate\Http\Request;
use App\Conversations\ExampleConversation;

class BotManLoginController extends Controller
{

    public function loginWithSlack()
    {
    	return view('botman.login.slack');
    }

    public function redirectFromSlack(Request $request, Client $client)
    {
        $url = 'https://slack.com/api/oauth.access';
        $response = $client->request('POST', $url, [
        	'form_params' => [
        		'client_id' => config('services.botman.slack_client_id'),
        		'client_secret' => config('services.botman.slack_client_secret'),
        		'code' => $request->get('code'),
        	]
    	]);
    	if ($response->getStatusCode() === 200) {
    		$responseData = json_decode($response->getBody());
    		return view('botman.login.slack_redirect', ['responseData' => $responseData]);
    	}
    	dd( (string)$response->getBody());
    }

}
