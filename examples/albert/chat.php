<?php

declare(strict_types=1);

use PhpLlm\LlmChain\Chain\Chain;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\PlatformFactory;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;

require_once dirname(__DIR__).'/../vendor/autoload.php';

// Albert API configuration
$albertApiKey = $_ENV['ALBERT_API_KEY'] ?? null;
$albertApiUrl = $_ENV['ALBERT_API_URL'] ?? null;

if (empty($albertApiKey)) {
    echo 'Please set the ALBERT_API_KEY environment variable.'.\PHP_EOL;
    exit(1);
}

if (empty($albertApiUrl)) {
    echo 'Please set the ALBERT_API_URL environment variable (e.g., https://your-albert-instance.com).'.\PHP_EOL;
    exit(1);
}

// Albert API is OpenAI-compatible, so we use the OpenAI platform factory
// with a custom base URL pointing to your Albert instance
$platform = PlatformFactory::create(
    apiKey: $albertApiKey,
    baseUrl: rtrim((string) $albertApiUrl, '/').'/v1/', // Ensure proper URL format
);

// Use a model name - Albert will route to the appropriate backend
$model = new GPT('gpt-4o');

$chain = new Chain($platform, $model);

$messages = new MessageBag(
    Message::forSystem('You are a helpful AI assistant powered by Albert API.'),
    Message::ofUser('Hello! Can you tell me about the French government\'s AI initiatives?'),
);

$response = $chain->call($messages);

echo $response->getContent().\PHP_EOL;
