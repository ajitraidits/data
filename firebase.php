<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class FirebaseController extends Controller
{

    public function index()
    {
        // Set the recipient device token
        $deviceToken = '9c4cadf536cb82b2895a767725093642aea828fe375b4b120f1f45340cac8bbe';
        $deviceToken = $this->ios_token($deviceToken);

// Set the message payload
        $message = array(
            "title" => "Notification Title",
            "body" => "Notification Body",
            "sound" => "default"
        );


// Set the FCM API endpoint URL
        $url = "https://fcm.googleapis.com/fcm/send";

// Set the server key
        $serverKey = env('FCM_SERVER_KEY');

// Set the headers
        $headers = array(
            "Authorization: key=" . $serverKey,
            "Content-Type: application/json"
        );

// Set the silent request to body

        $fields = [
            'to' => $deviceToken,
            "content_available" => true,
            "apns-priority" => 5,
            "data" => [
                "title" => "silent"
            ]
        ];

// Send the request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);
        dd($result);


    }


    public function sendNotification(Request $request)
    {
        $firebaseToken = '9c4cadf536cb82b2895a767725093642aea828fe375b4b120f1f45340cac8bbe';
        $firebaseToken = $this->ios_token($firebaseToken);
//        dd($firebaseToken);
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
        curl_close($ch);
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
            'sandbox' => true,
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
