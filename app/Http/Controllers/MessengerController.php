<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use pimax\FbBotApp;
use pimax\Messages\Message;
use Twitter;

class MessengerController extends Controller
{
  public function webhook()
  {
    $local_verify_token = env('WEBHOOK_VERIFY_TOKEN');
    $hub_verify_token = \Input::get('hub_verify_token');

    if($local_verify_token == $hub_verify_token) {
      return \Input::get('hub_challenge');
    }
    else return "Bad verify token";
  }

  public function webhook_post()
  {
    // get message input
    $input = \Input::all();
    $recipient = $input['entry'][0]['messaging'][0]['sender']['id'];
    $input_text = strtolower($input['entry'][0]['messaging'][0]['message']['text']);

    // Log variables into log file
    // \Log::info(print_r($input_text, 1));

    // create a bot instance
    $token = env('PAGE_ACCESS_TOKEN');
    $bot = new FbBotApp($token);

    // handle input
    if( in_array($input_text, ["help", "help me", "i need help", "need help"]) ) {
      $text = "How Can I Help?";
    }
    elseif( in_array($input_text, ["berlin", "tel aviv", "los angeles", "new york"]) ) {
      $trends = Twitter::getTrendsPlace(['id' => 1968212]);
      $text = "Tranding in " . $input_text . ": \n";

      foreach ($trends[0]->trends as $index => $trend) {
        if($index > 9){
          break;
        }
        $text .= $index+1 . ". " . $trend->name . "\n";
        \Log::info("\n" . $index+1 . ". " . $trend->name);
      }
    }
    elseif($input_text == "hi") {
      $text = "Hi Back!!";
    }
    else $text = "I didn't catch that";

    // send message
    $message = new Message($recipient, $text);
    $bot->send($message);
  }
}
