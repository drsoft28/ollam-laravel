<?php
namespace Drsoft28\OllamaLaravel;

use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class OllamaClient
{
    protected $baseUrl;
    protected $model;
    protected $prompt;
    protected $options = [];
    protected $handleCallback=null;
    protected $keep_alive=300; //default 5m

    static function client($model=null){
        return new OllamaClient(config('ollama.base_url'), $model??config('ollama.model'));
    }

    public function __construct($baseUrl, $model=null)
    {
        $this->baseUrl = $baseUrl;
        $this->model = $model;
    }
    public function baseUrl($baseUrl):self{
        $this->baseUrl = $baseUrl;
        return $this;
    }
    public function model($model):self{
        $this->model = $model;
        return $this;
    }
    public function keep_alive($keep_alive):self{
        $this->keep_alive = $keep_alive;
        return $this;
    }

    public function prompt($prompt):self{
        $this->prompt = $prompt;
        return $this;
    }

    public function options($options):self{
        $this->options = $options;
        return $this;
    }

    public function appendOptions(array $append):self{
        $this->options = [...$this->options,...$append];
        return $this;
    }

    public function callback(callable $callback):self{
        
        $this->handleCallback=$callback;
        return $this;
    }

    public function generate(){
        $playload = array_merge([
            'model' => $this->model,
            'prompt' => $this->prompt,
        ], $this->options);
        $this->options($playload);
        try {
            return $this->ask('POST','/api/generate');
        } catch (RequestException $e) {
            throw new \Exception($e->getMessage());
        }
    }
  
   function hasCallback(){
    return isset($this->handleCallback) && is_callable($this->handleCallback);
   }
    public function chat($messages){
        $playload = array_merge([
            'model' => $this->model,
            'messages' => $messages,
        ], $this->options);
        $this->options($playload);
        try {
            return $this->ask('POST','/api/chat');
        } catch (RequestException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function show($sourceModel=null,$verbose=false){
        $playload = array_merge([
            'model' => $sourceModel??$this->model,
           'verbose' => $verbose,
        ], $this->options);
        $this->options($playload);
        try {
            return $this->ask('POST','/api/show');
        } catch (RequestException $e) {
            throw new \Exception($e->getMessage());
        }
    }
    public function copy($newModel,$sourceModel=null){
        $playload = array_merge([
            'source' => $sourceModel??$this->model,
            'destination' => $newModel,
        ], $this->options);
        $this->options($playload);
        try {
            return $this->ask('POST','/api/copy');
        } catch (RequestException $e) {
            throw new \Exception($e->getMessage());
        }
    }
    public function delete($sourceModel=null){
        $playload = array_merge([
            'model' => $sourceModel??$this->model,
           
        ], $this->options);
        $this->options($playload);
        try {
            return $this->ask('DELETE','/api/delete');
        } catch (RequestException $e) {
            throw new \Exception($e->getMessage());
        }
    }
    public function pull($model=null,$insecure=false){
        $playload = array_merge([
            'model' => $model??$this->model,
            'insecure' => $insecure,
        ], $this->options);
        $this->options($playload);
        try {
            return $this->ask('POST','/api/pull');
        } catch (RequestException $e) {
            throw new \Exception($e->getMessage());
        }
    }
    public function embeddings($model){
        $playload = array_merge([
            'model' => $model??$this->model,
            'keep_alive' => $this->keep_alive,
        ], $this->options);
        $this->options($playload);
        try {
            return $this->ask('POST','/api/embeddings');
        } catch (RequestException $e) {
            throw new \Exception($e->getMessage());
        }
    }
    
    // list local models
    public function getLocalModels(){
        try {
            return $this->ask('GET','/api/tags');
        } catch (RequestException $e) {
            throw new \Exception($e->getMessage());
        }
    }
  

    // list running models
    public function getRunningModels(){
        try {
            return $this->ask('GET','/api/ps');
        } catch (RequestException $e) {
            throw new \Exception($e->getMessage());
        }
    }
   
    public function ask($method,$uri){
        $client = new Client();
        $url = $this->baseUrl . $uri;
        $playload =$this->options;
        $request = $client->request($method,$url, [
            'json' => $playload,
            'stream' => $this->hasCallback(),
        ]);
        $body = $request->getBody();
        if($this->hasCallback()){
            $buffer = ''; // Buffer to accumulate partial JSON strings
            $response = '';
        while (!$body->eof()) {
            $line = $body->read(1024);
            // Append the chunk to the buffer
            $buffer .= $line;
            $response .=$line;
            // Parse the buffer for complete JSON objects
            while (($jsonObject = OllamaHelper::extractJsonObject($buffer)) !== null) {
                // Remove the parsed JSON object from the buffer
                $buffer = ltrim(substr($buffer, strlen($jsonObject)));

                // Decode the JSON object
                $data = json_decode($jsonObject, true);
                call_user_func($this->handleCallback, $data, $jsonObject);
                
            }
        }
        }else{
            $response = $body;
        }
        return json_decode($response, true);
    }
}