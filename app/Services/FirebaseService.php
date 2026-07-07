<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    /**
     * Get OAuth 2.0 Access Token from Google APIs using Service Account.
     */
    protected function getAccessToken()
    {
        return Cache::remember('firebase_fcm_access_token', 50 * 60, function () {
            $configPath = config('services.firebase.service_account_path', 'storage/app/firebase-service-account.json');
            $absolutePath = base_path($configPath);

            if (!file_exists($absolutePath)) {
                Log::error("Firebase service account file not found at: {$absolutePath}");
                return null;
            }

            try {
                $serviceAccount = json_decode(file_get_contents($absolutePath), true);
                $privateKey = $serviceAccount['private_key'];
                $clientEmail = $serviceAccount['client_email'];

                $now = time();
                $payload = [
                    'iss' => $clientEmail,
                    'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                    'aud' => 'https://oauth2.googleapis.com/token',
                    'exp' => $now + 3600,
                    'iat' => $now
                ];

                $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
                
                $base64UrlHeader = $this->base64UrlEncode($header);
                $base64UrlPayload = $this->base64UrlEncode(json_encode($payload));

                $signingInput = "{$base64UrlHeader}.{$base64UrlPayload}";

                if (!openssl_sign($signingInput, $signature, $privateKey, 'SHA256')) {
                    throw new \Exception("Failed to sign JWT with OpenSSL.");
                }

                $base64UrlSignature = $this->base64UrlEncode($signature);
                $jwt = "{$signingInput}.{$base64UrlSignature}";

                $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt
                ]);

                if ($response->failed()) {
                    throw new \Exception("OAuth token request failed: " . $response->body());
                }

                return $response->json('access_token');

            } catch (\Exception $e) {
                Log::error("Firebase OAuth Token Error: " . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Send Web Push notification via FCM v1 API.
     *
     * Uses a DATA-ONLY message (no top-level 'notification' field) so that FCM
     * always routes the message through the service worker's onBackgroundMessage()
     * handler – even when the screen is off.  The service worker then calls
     * self.registration.showNotification() with full control over sound/vibration.
     */
    public function sendNotification($token, $title, $body, $data = [])
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return false;
        }

        $configPath = config('services.firebase.service_account_path', 'storage/app/firebase-service-account.json');
        $absolutePath = base_path($configPath);

        if (!file_exists($absolutePath)) {
            return false;
        }

        $serviceAccount = json_decode(file_get_contents($absolutePath), true);
        $projectId = $serviceAccount['project_id'] ?? config('services.firebase.project_id', 'foms-bms');

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $clickAction = isset($data['class_hour_id'])
            ? url("/student/classes/join/" . $data['class_hour_id'])
            : url("/student/dashboard");

        $payload = [
            'message' => [
                'token' => $token,

                // ── DATA-ONLY — no top-level 'notification' key ──────────────────
                // With no 'notification' field, FCM never auto-displays a notification.
                // The service worker's onBackgroundMessage() always fires, giving us
                // full control to show a notification with the sound/vibration we want.
                // Title & body are embedded in 'data' so the SW can read them.
                'data' => array_map('strval', array_merge([
                    'title' => $title,
                    'body'  => $body,
                    'type'  => 'buzzer',
                ], $data)),

                // ── WEBPUSH headers ──────────────────────────────────────────────
                // Urgency:high  → FCM delivers immediately, bypassing battery-saver queuing.
                // TTL:60        → If device is unreachable, retry for 60 seconds only.
                'webpush' => [
                    'headers' => [
                        'Urgency' => 'high',
                        'TTL'     => '60',
                    ],
                    'fcm_options' => [
                        'link' => $clickAction,
                    ],
                ],
            ]
        ];

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$accessToken}",
            'Content-Type'  => 'application/json',
        ])->post($url, $payload);

        if ($response->failed()) {
            Log::error("FCM Send Notification Failed: " . $response->body());
            return false;
        }

        return true;
    }

    /**
     * Base64URL encoding helper.
     */
    protected function base64UrlEncode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}
