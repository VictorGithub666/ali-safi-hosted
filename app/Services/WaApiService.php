<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class WaApiService
{
    protected ?Client $client = null;
    protected ?string $instanceId = null;
    protected ?string $apiToken = null;

    public function __construct()
    {
        // Get credentials from config (which reads from .env)
        $this->instanceId = config('services.waapi.instance_id');
        $this->apiToken = config('services.waapi.api_token');
        
        // Only initialize client if credentials are present
        if ($this->instanceId && $this->apiToken) {
            $this->client = new Client([
                'base_uri' => "https://api.waapi.app/api/v1/instances/{$this->instanceId}/client/",
                'timeout'  => 30.0,
            ]);
            Log::info('WaAPI Service initialized', ['instance_id' => $this->instanceId]);
        } else {
            Log::warning('WaAPI credentials not configured. Please add WAAPI_INSTANCE_ID and WAAPI_API_TOKEN to your .env file');
        }
    }

    /**
     * Check if WaAPI is configured
     */
    public function isConfigured(): bool
    {
        return $this->client !== null && $this->instanceId && $this->apiToken;
    }

    /**
     * Send a text message to a WhatsApp number.
     *
     * @param string $to The recipient's WhatsApp number in international format (e.g., 254748109181 or +254748109181)
     * @param string $message The message content
     * @return array
     */
    public function sendTextMessage(string $to, string $message): array
    {
        // Check if WaAPI is configured
        if (!$this->isConfigured()) {
            Log::warning('WaAPI not configured, message not sent', ['to' => $to]);
            return ['error' => true, 'message' => 'WaAPI not configured', 'type' => 'config'];
        }

        // Normalize the phone number format for WaAPI
        // Remove '+' if present
        $phoneNumber = ltrim($to, '+');
        // Remove any existing @c.us suffix
        $phoneNumber = str_replace('@c.us', '', $phoneNumber);
        // Add the required @c.us suffix for WaAPI
        $chatId = $phoneNumber . '@c.us';

        $payload = [
            'chatId' => $chatId,
            'message' => $message,
        ];

        Log::info('WaAPI: Sending message', [
            'to' => $to,
            'normalized_to' => $chatId,
            'message_length' => strlen($message),
        ]);

        try {
            $response = $this->client->post('action/send-message', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $responseBody = json_decode($response->getBody()->getContents(), true);
            Log::info('WaAPI: Message sent successfully', [
                'to' => $chatId,
                'response_status' => $responseBody['status'] ?? 'unknown',
                'response' => $responseBody
            ]);
            return $responseBody;

        } catch (ConnectException $e) {
            // Connection error - API is unreachable (this exception does NOT have hasResponse())
            $errorMessage = 'Connection failed: ' . $e->getMessage();
            Log::error('WaAPI: Connection error', [
                'to' => $chatId,
                'error' => $errorMessage,
                'request_payload' => $payload,
            ]);
            return ['error' => true, 'message' => $errorMessage, 'type' => 'connection'];
            
        } catch (BadResponseException $e) {
            // Bad response error - API responded with error status (this exception HAS hasResponse())
            $responseBody = null;
            $statusCode = null;
            
            if ($e->hasResponse()) {
                $statusCode = $e->getResponse()->getStatusCode();
                try {
                    $responseBody = $e->getResponse()->getBody()->getContents();
                    // Try to decode as JSON for better logging
                    $decoded = json_decode($responseBody, true);
                    if (is_array($decoded)) {
                        $responseBody = $decoded;
                    }
                } catch (\Exception $bodyException) {
                    $responseBody = 'Could not read response body: ' . $bodyException->getMessage();
                }
            }
            
            Log::error('WaAPI: Bad response from API', [
                'to' => $chatId,
                'status_code' => $statusCode,
                'error' => $e->getMessage(),
                'response_body' => $responseBody,
                'request_payload' => $payload,
            ]);
            return ['error' => true, 'message' => $e->getMessage(), 'response' => $responseBody, 'type' => 'bad_response', 'status_code' => $statusCode];
            
        } catch (GuzzleException $e) {
            // Catch any other Guzzle exceptions safely
            $errorDetails = [
                'to' => $chatId,
                'error_class' => get_class($e),
                'error' => $e->getMessage(),
                'request_payload' => $payload,
            ];
            
            // Safely check for response body if exception has it
            if ($e instanceof BadResponseException && $e->hasResponse()) {
                try {
                    $errorDetails['response_body'] = $e->getResponse()->getBody()->getContents();
                } catch (\Exception $bodyException) {
                    $errorDetails['response_body'] = 'Could not read response body';
                }
            }
            
            Log::error('WaAPI: Guzzle error', $errorDetails);
            return ['error' => true, 'message' => $e->getMessage(), 'type' => 'guzzle_error'];
            
        } catch (\Exception $e) {
            // Catch any other unexpected exceptions
            Log::error('WaAPI: Unexpected error', [
                'to' => $chatId,
                'error_class' => get_class($e),
                'error' => $e->getMessage(),
                'request_payload' => $payload,
            ]);
            return ['error' => true, 'message' => $e->getMessage(), 'type' => 'unexpected'];
        }
    }
}