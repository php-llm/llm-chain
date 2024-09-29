<?php

use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Model\Language\Llama;
use PhpLlm\LlmChain\Platform\Ollama;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['OLLAMA_HOST_URL'])) {
    echo 'Please set the OLLAMA_HOST_URL environment variable.'.PHP_EOL;
    exit(1);
}

$platform = new Ollama(HttpClient::create(), $_ENV['OLLAMA_HOST_URL']);
$llm = new Llama($platform);

$chain = new Chain($llm);
$messages = new MessageBag(
    Message::forSystem('You are a helpful assistant.'),
    Message::ofUser('Tina has one brother and one sister. How many sisters do Tina\'s siblings have?'),
);
$response = $chain->call($messages);

echo $response->getContent().PHP_EOL;
