<?php

use PhpLlm\LlmChain\Anthropic\Claude;
use PhpLlm\LlmChain\ChatChain;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
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
$claude = new Claude($httpClient, getenv('ANTHROPIC_API_KEY'));
$chatChain = new ChatChain($claude);
$messages = new MessageBag(
    Message::forSystem('You are a pirate and you write funny.'),
);
$response = $chatChain->call(Message::ofUser('What is the Symfony framework?'), $messages);

echo $response.PHP_EOL;
