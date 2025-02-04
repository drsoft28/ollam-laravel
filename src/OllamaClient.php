<?php
namespace Drsoft28\OllamaLaravel;

use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Class OllamaClient
 *
 * A client for interacting with the Ollama API endpoints.
 */
class OllamaClient
{
    // Base URL for the Ollama API
    protected $baseUrl;
    
    // Model identifier used in API requests
    protected $model;
    
    // Prompt text for generating content
    protected $prompt;
    
    // Additional options merged into API payloads
    protected $options = [];
    
    // Optional callback to process streamed response data
    protected $handleCallback = null;
    
    // Keep-alive timeout for streaming responses (default 5 minutes)
    protected $keep_alive = 300;

    /**
     * Static method to instantiate the client using configuration settings.
     *
     * @param string|null $model Optional model override.
     * @return OllamaClient
     */
    static function client($model = null) {
        // Use configured base URL and model (if not provided) from config files
        return new OllamaClient(config('ollama.base_url'), $model ?? config('ollama.model'));
    }

    /**
     * Constructor for OllamaClient.
     *
     * @param string $baseUrl Base URL for the API.
     * @param string|null $model Optional model identifier.
     */
    public function __construct($baseUrl, $model = null)
    {
        $this->baseUrl = $baseUrl;
        $this->model = $model;
    }

    /**
     * Set a new base URL.
     *
     * @param string $baseUrl
     * @return self
     */
    public function baseUrl($baseUrl): self {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    /**
     * Set the model identifier.
     *
     * @param string $model
     * @return self
     */
    public function model($model): self {
        $this->model = $model;
        return $this;
    }

    /**
     * Set the keep-alive timeout for streaming responses.
     *
     * @param int $keep_alive Timeout in seconds.
     * @return self
     */
    public function keep_alive($keep_alive): self {
        $this->keep_alive = $keep_alive;
        return $this;
    }

    /**
     * Set the prompt for a generate request.
     *
     * @param string $prompt
     * @return self
     */
    public function prompt($prompt): self {
        $this->prompt = $prompt;
        return $this;
    }

    /**
     * Set the options to be merged with API request payloads.
     *
     * @param array $options
     * @return self
     */
    public function options($options): self {
        $this->options = $options;
        return $this;
    }

    /**
     * Append additional options to the existing options array.
     *
     * @param array $append Additional options to merge.
     * @return self
     */
    public function appendOptions(array $append): self {
        // Merge existing options with new options using spread operator.
        $this->options = [...$this->options, ...$append];
        return $this;
    }

    /**
     * Set a callback to process streamed response data.
     *
     * @param callable $callback Callback function to handle data.
     * @return self
     */
    public function callback(callable $callback): self {
        $this->handleCallback = $callback;
        return $this;
    }

    /**
     * Generate a response based on the prompt and options.
     *
     * @return array Decoded JSON response from the API.
     * @throws \Exception if the API request fails.
     */
    public function generate(){
        // Merge the model and prompt with any additional options.
        $playload = array_merge([
            'model' => $this->model,
            'prompt' => $this->prompt,
        ], $this->options);
        // Update the options with the payload
        $this->options($playload);
        try {
            // Send the request to the /api/generate endpoint using POST.
            return $this->ask('POST', '/api/generate');
        } catch (RequestException $e) {
            throw new \Exception($e->getMessage());
        }
    }
  
    /**
     * Check if a valid callback is set for streaming responses.
     *
     * @return bool True if a callable callback exists; otherwise false.
     */
    function hasCallback(){
        return isset($this->handleCallback) && is_callable($this->handleCallback);
    }

    /**
     * Send chat messages to the API.
     *
     * @param array $messages Array of messages for the chat.
     * @return array Decoded JSON response from the API.
     * @throws \Exception if the API request fails.
     */
    public function chat($messages){
        // Merge model and messages with any additional options.
        $playload = array_merge([
            'model' => $this->model,
            'messages' => $messages,
        ], $this->options);
        $this->options($playload);
        try {
            // Send the request to the /api/chat endpoint using POST.
            return $this->ask('POST', '/api/chat');
        } catch (RequestException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Retrieve details for a specified model.
     *
     * @param string|null $sourceModel Model to show; defaults to current model.
     * @param bool $verbose Whether to include verbose output.
     * @return array Decoded JSON response from the API.
     * @throws \Exception if the API request fails.
     */
    public function show($sourceModel = null, $verbose = false){
        // Merge the model (or provided sourceModel) and verbose flag with options.
        $playload = array_merge([
            'model' => $sourceModel ?? $this->model,
            'verbose' => $verbose,
        ], $this->options);
        $this->options($playload);
        try {
            // Send the request to the /api/show endpoint using POST.
            return $this->ask('POST', '/api/show');
        } catch (RequestException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Copy a model to create a new model.
     *
     * @param string $newModel New model identifier.
     * @param string|null $sourceModel Source model identifier; defaults to current model.
     * @return array Decoded JSON response from the API.
     * @throws \Exception if the API request fails.
     */
    public function copy($newModel, $sourceModel = null){
        // Merge the source and destination models with any additional options.
        $playload = array_merge([
            'source' => $sourceModel ?? $this->model,
            'destination' => $newModel,
        ], $this->options);
        $this->options($playload);
        try {
            // Send the request to the /api/copy endpoint using POST.
            return $this->ask('POST', '/api/copy');
        } catch (RequestException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Delete a specified model.
     *
     * @param string|null $sourceModel Model to delete; defaults to current model.
     * @return array Decoded JSON response from the API.
     * @throws \Exception if the API request fails.
     */
    public function delete($sourceModel = null){
        // Merge the model to delete with any additional options.
        $playload = array_merge([
            'model' => $sourceModel ?? $this->model,
        ], $this->options);
        $this->options($playload);
        try {
            // Send the request to the /api/delete endpoint using DELETE.
            return $this->ask('DELETE', '/api/delete');
        } catch (RequestException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Pull a model from a remote source.
     *
     * @param string|null $model Model to pull; defaults to current model.
     * @param bool $insecure Whether to allow insecure connections.
     * @return array Decoded JSON response from the API.
     * @throws \Exception if the API request fails.
     */
    public function pull($model = null, $insecure = false){
        // Merge the model and insecure flag with any additional options.
        $playload = array_merge([
            'model' => $model ?? $this->model,
            'insecure' => $insecure,
        ], $this->options);
        $this->options($playload);
        try {
            // Send the request to the /api/pull endpoint using POST.
            return $this->ask('POST', '/api/pull');
        } catch (RequestException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Retrieve embeddings for a model.
     *
     * @param string|null $model Model to use; defaults to current model.
     * @return array Decoded JSON response from the API.
     * @throws \Exception if the API request fails.
     */
    public function embeddings($model){
        // Merge the model and keep_alive option with any additional options.
        $playload = array_merge([
            'model' => $model ?? $this->model,
            'keep_alive' => $this->keep_alive,
        ], $this->options);
        $this->options($playload);
        try {
            // Send the request to the /api/embeddings endpoint using POST.
            return $this->ask('POST', '/api/embeddings');
        } catch (RequestException $e) {
            throw new \Exception($e->getMessage());
        }
    }
    
    /**
     * Retrieve a list of local models.
     *
     * @return array Decoded JSON response containing local models.
     * @throws \Exception if the API request fails.
     */
    public function getLocalModels(){
        try {
            // Send the request to the /api/tags endpoint using GET.
            return $this->ask('GET', '/api/tags');
        } catch (RequestException $e) {
            throw new \Exception($e->getMessage());
        }
    }
  
    /**
     * Retrieve a list of running models.
     *
     * @return array Decoded JSON response containing running models.
     * @throws \Exception if the API request fails.
     */
    public function getRunningModels(){
        try {
            // Send the request to the /api/ps endpoint using GET.
            return $this->ask('GET', '/api/ps');
        } catch (RequestException $e) {
            throw new \Exception($e->getMessage());
        }
    }
   
    /**
     * Execute an API request.
     *
     * @param string $method HTTP method (GET, POST, DELETE, etc.).
     * @param string $uri API endpoint URI.
     * @return array Decoded JSON response from the API.
     * @throws \Exception if the API request fails.
     */
    public function ask($method, $uri){
        $client = new Client(); // Create a new Guzzle HTTP client.
        $url = $this->baseUrl . $uri; // Construct the full API URL.
        $playload = $this->options; // Set the payload for the request.
        
        // Make the HTTP request with JSON payload.
        $request = $client->request($method, $url, [
            'json' => $playload,
            // Enable streaming if a callback is defined.
            'stream' => $this->hasCallback(),
        ]);
        
        $body = $request->getBody(); // Get the response body.
        
        if ($this->hasCallback()) {
            // If a callback is set, process the response in a streaming manner.
            $buffer = ''; // Buffer to accumulate partial JSON strings.
            $response = '';
            while (!$body->eof()) {
                // Read chunks of 1024 bytes.
                $line = $body->read(1024);
                // Append the chunk to the buffer and overall response.
                $buffer .= $line;
                $response .= $line;
                // Extract complete JSON objects from the buffer.
                while (($jsonObject = OllamaHelper::extractJsonObject($buffer)) !== null) {
                    // Remove the parsed JSON object from the buffer.
                    $buffer = ltrim(substr($buffer, strlen($jsonObject)));
                    // Decode the JSON object.
                    $data = json_decode($jsonObject, true);
                    // Call the user-defined callback with the parsed data and raw JSON.
                    call_user_func($this->handleCallback, $data, $jsonObject);
                }
            }
        } else {
            // If no callback, read the entire response body at once.
            $response = $body;
        }
        
        // Decode and return the full JSON response.
        return json_decode($response, true);
    }
}
