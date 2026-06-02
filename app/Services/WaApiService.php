<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class WaApiService
{
    protected Client $client;
    protected string $instanceId;
    protected string $apiToken;

    public function __construct()
    {
        // You will store your credentials in the .env file
        $this->instanceId = config('services.waapi.instance_id', env('WAAPI_INSTANCE_ID'));
        $this->apiToken = config('services.waapi.api_token', env('WAAPI_API_TOKEN'));
        
        $this->client = new Client([
            'base_uri' => "https://api.waapi.app/api/v1/instances/{$this->instanceId}/client/",
            'timeout'  => 30.0,
        ]);
    }

    /**
     * Send a text message to a WhatsApp number.
     *
     * @param string $to The recipient's WhatsApp number in international format (e.g., 254748109181)
     * @param string $message The message content
     * @return array
     */
    public function sendTextMessage(string $to, string $message): array
    {
        // Ensure the number is in the correct format.
        // WaAPI's documentation often expects the number with the country code and without '+'.
        $to = ltrim($to, '+'); // Remove '+' if present.
        $to = str_replace('@c.us', '', $to); // Remove any suffix if present.

        $payload = [
            'to' => $to,
            'text' => $message,
        ];

        try {
            $response = $this->client->post('send-text', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Accept' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $responseBody = json_decode($response->getBody()->getContents(), true);
            Log::info('WaAPI: Message sent successfully', ['to' => $to, 'response' => $responseBody]);
            return $responseBody;

        } catch (GuzzleException $e) {
            Log::error('WaAPI: Failed to send message', [
                'to' => $to,
                'error' => $e->getMessage(),
                'response_body' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null,
            ]);
            return ['error' => true, 'message' => $e->getMessage()];
        }
    }
}