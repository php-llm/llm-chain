<?php

use Symfony\Component\HttpClient\HttpClient;
use SymfonyLlm\LlmChain\Message\Message;
use SymfonyLlm\LlmChain\Message\MessageBag;
use SymfonyLlm\LlmChain\OpenAI\ChatModel;
use SymfonyLlm\LlmChain\OpenAI\OpenAIClient;

require_once __DIR__.'/../vendor/autoload.php';

$openAiClient = new OpenAIClient(HttpClient::create(), getenv('OPENAI_API_KEY'));
$chatModel = new ChatModel($openAiClient);
$messages = new MessageBag(
    Message::forSystem('You are a pirate and you write funny.'),
    Message::ofUser('What is the Symfony framework?'),
);
$response = $chatModel->call($messages);

echo $response['choices'][0]['message']['content'].PHP_EOL;
