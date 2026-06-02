<?php

namespace App\Services;

use GuzzleHttp\Client;
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
     * @param string $to The recipient's WhatsApp number in international format (e.g., 254748109181)
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

        // Ensure the number is in the correct format.
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

        } catch (ConnectException $e) {
            // Connection error - API is unreachable (this exception does NOT have hasResponse())
            $errorMessage = 'Connection failed: ' . $e->getMessage();
            Log::error('WaAPI: Connection error', [
                'to' => $to,
                'error' => $errorMessage,
            ]);
            return ['error' => true, 'message' => $errorMessage, 'type' => 'connection'];
            
        } catch (RequestException $e) {
            // Request error - API responded with error status (this exception HAS hasResponse())
            $responseBody = null;
            if ($e->hasResponse()) {
                $responseBody = $e->getResponse()->getBody()->getContents();
            }
            
            Log::error('WaAPI: Request failed', [
                'to' => $to,
                'error' => $e->getMessage(),
                'response_body' => $responseBody,
            ]);
            return ['error' => true, 'message' => $e->getMessage(), 'response' => $responseBody, 'type' => 'request'];
            
        } catch (GuzzleException $e) {
            // Catch any other Guzzle exceptions
            Log::error('WaAPI: Unexpected Guzzle error', [
                'to' => $to,
                'error_class' => get_class($e),
                'error' => $e->getMessage(),
            ]);
            return ['error' => true, 'message' => $e->getMessage(), 'type' => 'guzzle_error'];
            
        } catch (\Exception $e) {
            // Catch any other unexpected exceptions
            Log::error('WaAPI: Unexpected error', [
                'to' => $to,
                'error_class' => get_class($e),
                'error' => $e->getMessage(),
            ]);
            return ['error' => true, 'message' => $e->getMessage(), 'type' => 'unexpected'];
        }
    }
}