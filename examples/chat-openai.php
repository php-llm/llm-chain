<?php

use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\OpenAI\ChatModel;
use PhpLlm\LlmChain\OpenAI\OpenAIClient;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';

$openAiClient = new OpenAIClient(HttpClient::create(), getenv('OPENAI_API_KEY'));
$chatModel = new ChatModel($openAiClient);
$messages = new MessageBag(
    Message::forSystem('You are a pirate and you write funny.'),
    Message::ofUser('What is the Symfony framework?'),
);
$response = $chatModel->call($messages);

echo $response['choices'][0]['message']['content'].PHP_EOL;
