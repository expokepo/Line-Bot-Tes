<?php
require __DIR__ . '/vendor/autoload.php';

use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;

// set false for production
$pass_signature = true;

// set LINE channel_access_token and channel_secret
$channel_access_token = "58wlbZElqQz8crjcCxutBFeUa69Ph02ST1mp9ShlHbzARPXH+w30u2wOd0FH+4bAo2zOujyvVOry+qQhQSNvUWoZvOMixyPWmCVuz0Rf1F3jvvLsdScBPtjq3xYq0voyFnAAY19xYDluE3hkvKg8eAdB04t89/1O/w1cDnyilFU=";
$channel_secret = "82d052402481d404b3e6a3a592e1cd7d";

// inisiasi objek bot
$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);

$configs =  [
    'settings' => ['displayErrorDetails' => true],
];
$app = new Slim\App($configs);

// buat route untuk url homepage
$app->get('/', function($req, $res)
{
  echo "Welcome at Slim Framework";
});

// buat route untuk webhook
$app->post('/webhook', function ($request, $response) use ($bot, $pass_signature)
{
    // get request body and line signature header
    $body        = file_get_contents('php://input');
    $signature = isset($_SERVER['HTTP_X_LINE_SIGNATURE']) ? $_SERVER['HTTP_X_LINE_SIGNATURE'] : '';

    // log body and signature
    file_put_contents('php://stderr', 'Body: '.$body);

    if($pass_signature === false)
    {
        // is LINE_SIGNATURE exists in request header?
        if(empty($signature)){
            return $response->withStatus(400, 'Signature not set');
        }

        // is this request comes from LINE?
        if(! SignatureValidator::validateSignature($body, $channel_secret, $signature)){
            return $response->withStatus(400, 'Invalid signature');
        }
    }

    // kode aplikasi nanti disini
    $data = json_decode($body, true);
if(is_array($data['events'])){
    foreach ($data['events'] as $event)
    {
        if ($event['type'] == 'message')
        {
            if($event['message']['type'] == 'text')
            {
                // send same message as reply to user
                //$result = $bot->replyText($event['replyToken'], $event['message']['text']);
                //or this
               //$result = $bot->replyText($replyToken, 'ini pesan balasan');
               $result = $bot->replyText($event['replyToken'], 'ini pesan balasan');

                // or we can use replyMessage() instead to send reply message
                // $textMessageBuilder = new TextMessageBuilder($event['message']['text']);
                // $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                // or this
                //$textMessageBuilder = new TextMessageBuilder('ini pesan balasan');
                //$bot->replyMessage($replyToken, $textMessageBuilder);
                
                // $textMessageBuilder1 = new TextMessageBuilder('ini pesan balasan pertama');
                // $textMessageBuilder2 = new TextMessageBuilder('ini pesan balasan kedua');
                // $stickerMessageBuilder = new StickerMessageBuilder(1, 106);

                // $multiMessageBuilder = new MultiMessageBuilder();
                // $multiMessageBuilder->add($textMessageBuilder1);
                // $multiMessageBuilder->add($textMessageBuilder2);
                // $multiMessageBuilder->add($stickerMessageBuilder);

                // $bot->replyMessage($replyToken, $multiMessageBuilder);


                return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
            }
        }
    }
}


});

$app->get('/pushmessage', function($req, $res) use ($bot)
{
    // send push message to user
    $userId = 'U6c7dfcb824ec19ff3c933613f359a7a9';
    $textMessageBuilder = new TextMessageBuilder('Halo, ini pesan push');
    $result = $bot->pushMessage($userId, $textMessageBuilder);
   
    return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
});


$app->run();
