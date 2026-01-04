<?php

namespace App\Helpers;

use Google\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationHelper
{
    public static function sendPush($token, $title, $body)
    {
        try {
            $client = new Client();
            $path = storage_path('app/firebase-auth.json');
            
            $client->setAuthConfig($path);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            
            $accessToken = $client->fetchAccessTokenWithAssertion()['access_token'];

            $authData = json_decode(file_get_contents($path), true);
            $projectId = $authData['project_id'];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json',
            ])->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'channel_id' => 'izin_channel',
                            'notification_priority' => 'PRIORITY_MAX',
                            'sound' => 'default',
                        ],
                    ],
                    'data' => [
                        'type' => 'permission_update',
                    ],
                ],
            ]);

            $result = $response->json();
            Log::info('FCM Response: ', $result);
            
            return $result;

        } catch (\Exception $e) {
            Log::error('FCM Error: ' . $e->getMessage());
            return false;
        }
    }
}