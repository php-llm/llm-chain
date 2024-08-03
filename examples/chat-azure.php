<?php

use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\OpenAI\AzureClient;
use PhpLlm\LlmChain\OpenAI\ChatModel;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';

$logger = new ConsoleLogger(new ConsoleOutput(ConsoleOutput::VERBOSITY_DEBUG));
$httpClient = HttpClient::create();
if ($httpClient instanceof LoggerAwareInterface) {
    $httpClient->setLogger($logger);
}
$azureClient = new AzureClient(
    $httpClient,
    getenv('AZURE_OPENAI_RESOURCE'),
    getenv('AZURE_OPENAI_DEPLOYMENT'),
    getenv('AZURE_OPENAI_VERSION'),
    getenv('AZURE_OPENAI_KEY'),
);
$chatModel = new ChatModel($azureClient);
$messages = new MessageBag(
    Message::forSystem('You are a pirate and you write funny.'),
    Message::ofUser('What is the Symfony framework?'),
);
$response = $chatModel->call($messages);

echo $response['choices'][0]['message']['content'].PHP_EOL;
