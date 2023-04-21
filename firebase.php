<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Kreait\Firebase\Messaging\CloudMessage;

class FirebaseController extends Controller
{

    public function index()
    {
        $messaging = app('firebase.messaging');
        $deviceToken = '36191649634f6bea969b9f8261ebc6544a40c8644731dd55da4e2fd0cb462850';
//        $message = CloudMessage::withTarget('token', '$deviceToken')
//            ->withData(['getLocation' => true]);

        $message = CloudMessage::fromArray([
            'to' => $deviceToken,
            'data' => ['getLocation' => true]
        ]);
        $data = $messaging->send($message);
        dd($data);
    }


    public function sendNotification(Request $request)
    {
        $firebaseToken = '50c07242f078cc15f1f411333dbca4529631f909f612ee4fac3c4df3c8ce58e8';
        $firebaseToken = $this->ios_token($firebaseToken);
        $SERVER_API_KEY = env('FCM_SERVER_KEY');

        $data = [
            'to' => $firebaseToken,
            'data' => ['getLocation' => true]
        ];
        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);
        dd($response);
    }

    // IOS Token
    public function ios_token($deviceToken)
    {
        $serverKey = env('FCM_SERVER_KEY');

        $headers = [
            'Authorization' => 'key=' . $serverKey,
            'Content-Type' => 'application/json',
        ];
        $fields = [
            'application' => "net.altinc.tomrex",
            'sandbox' => env("FCM_SANDBOX"),
            'apns_tokens' => array($deviceToken)
        ];

        $client = new Client();
        $request = $client->post("https://iid.googleapis.com/iid/v1:batchImport", [
            'headers' => $headers,
            "body" => json_encode($fields),
        ]);
        $response = json_decode($request->getBody()->getContents(), true);
        return $response['results'][0]['registration_token'];
    }
}
