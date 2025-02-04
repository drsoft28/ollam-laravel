# Ollama Laravel

Drsoft28/ollama-laravel is a Laravel package that provides a fluent and expressive API for interacting with the Ollama API. With this package, you can generate text, engage in chat sessions, manage models, retrieve embeddings, and much more through a convenient facade.

---

## Features

- **Generate Text:** Create responses based on a given prompt.
- **Chat:** Send and receive chat messages.
- **Model Management:** Show, copy, delete, and pull models.
- **Embeddings:** Retrieve embeddings for models.
- **Streaming Support:** Process streamed API responses using callbacks.
- **Local & Running Models:** Easily list models available locally or running.
- **Custom API Calls:** Utilize the `ask` function to extend API functionality.

---

## Installation

Install the package via Composer:

```bash
composer require drsoft28/ollama-laravel
```

Laravel's package auto-discovery will automatically register the service provider and facade. If you need to register them manually, add the following to the `providers` array in your `config/app.php`:

```php
Drsoft28\OllamaLaravel\OllamaServiceProvider::class,
```

And add the alias for the facade to the `aliases` array:

```php
'Ollama' => Drsoft28\OllamaLaravel\Facades\Ollama::class,
```

---

## Configuration

Publish the configuration file with Artisan:

```bash
php artisan vendor:publish --provider="Drsoft28\OllamaLaravel\OllamaServiceProvider"
```

This will create a configuration file named `ollama.php` in your `config` directory. You can update the following options as needed:

- **`base_url`**: The base URL for your Ollama API (e.g., `http://localhost:11434`).
- **`model`**: The default model identifier to use.
- _Additional options_ can also be configured if necessary.

---

## Usage

This package exposes a facade that allows you to interact with the Ollama API easily.

### Using the Facade

Import the facade and call methods directly on it:

```php
use Drsoft28\OllamaLaravel\Facades\Ollama;

// Generating a text response based on a prompt
$response = Ollama::prompt("Your prompt text here")
                  ->generate();

print_r($response);
```

### Overriding the Default Model

If you don't want to use the default model set in your configuration, you can override it by calling the `model` method on the facade. This allows you to specify a custom model for a particular call:

```php
use Drsoft28\OllamaLaravel\Facades\Ollama;

$response = Ollama::model('custom_model')
                  ->prompt("Your prompt text here")
                  ->generate();

print_r($response);
```

In this example, the `custom_model` will be used instead of the default model defined in your configuration file.

### Chat

Send chat messages to the API:

```php
use Drsoft28\OllamaLaravel\Facades\Ollama;

$messages = [
    ['role' => 'user', 'content' => 'Hello, how are you?']
];

$response = Ollama::chat($messages);

print_r($response);
```

### Model Management

#### Show a Model

```php
use Drsoft28\OllamaLaravel\Facades\Ollama;

$response = Ollama::show('model_name', true);
print_r($response);
```

#### Copy a Model

```php
use Drsoft28\OllamaLaravel\Facades\Ollama;

$response = Ollama::copy('new_model_name', 'existing_model_name');
print_r($response);
```

#### Delete a Model

```php
use Drsoft28\OllamaLaravel\Facades\Ollama;

$response = Ollama::delete('model_name');
print_r($response);
```

#### Pull a Model

```php
use Drsoft28\OllamaLaravel\Facades\Ollama;

$response = Ollama::pull('model_name', true);
print_r($response);
```

### Retrieve Embeddings

```php
use Drsoft28\OllamaLaravel\Facades\Ollama;

$response = Ollama::embeddings('model_name');
print_r($response);
```

### Listing Models

#### Local Models

```php
use Drsoft28\OllamaLaravel\Facades\Ollama;

$localModels = Ollama::getLocalModels();
print_r($localModels);
```

#### Running Models

```php
use Drsoft28\OllamaLaravel\Facades\Ollama;

$runningModels = Ollama::getRunningModels();
print_r($runningModels);
```

### Handling Streaming Responses

If you need to process responses as they stream in, you can register a callback:

```php
use Drsoft28\OllamaLaravel\Facades\Ollama;

Ollama::callback(function($data, $rawJson) {
    // Process each chunk of data as it arrives
    echo "Received chunk: " . print_r($data, true) . "\n";
});

$response = Ollama::prompt("Streaming prompt")
                  ->generate();
```

---

## Custom API Calls with the `ask` Function

The `ask` function is a standard internal method that underlies all API interactions within the package. It handles sending HTTP requests to specified endpoints using a provided HTTP method and body data from the options. This function is flexible enough to allow you to extend the package's functionality by calling additional API endpoints that are not explicitly defined in the package.

You can pass custom body data via the `options` method and then invoke the `ask` function to target any API endpoint. For example, if you have an endpoint `/api/custom` that requires additional parameters, you can make a custom call like this:

```php
use Drsoft28\OllamaLaravel\Facades\Ollama;

$response = Ollama::options([
    'custom_data' => 'Your custom data here',
])
->ask('POST', '/api/custom');

print_r($response);
```

This approach allows you to fill in the necessary data for any custom API call while leveraging the built-in functionality for handling requests and responses. The `ask` function merges your provided options with the required API parameters and sends a JSON request to the target endpoint. It supports both synchronous and streaming responses, with optional callback handling if streaming is enabled.

---

## Contributing

Contributions are welcome! Please fork the repository and submit pull requests. For major changes, open an issue first to discuss your ideas.

---

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

---

## Support

If you encounter any issues or have feature requests, please open an issue in the repository.

Enjoy using Drsoft28/Ollama Laravel, and happy coding!