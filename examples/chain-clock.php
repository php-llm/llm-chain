<?php

use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\OpenAI\ChatModel;
use PhpLlm\LlmChain\OpenAI\OpenAIClient;
use PhpLlm\LlmChain\ToolBox\ParameterAnalyzer;
use PhpLlm\LlmChain\ToolBox\Registry;
use PhpLlm\LlmChain\ToolBox\Tool\Clock;
use PhpLlm\LlmChain\ToolBox\ToolAnalyzer;
use PhpLlm\LlmChain\ToolChain;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Clock\Clock as SymfonyClock;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';

$logger = new ConsoleLogger(new ConsoleOutput(ConsoleOutput::VERBOSITY_DEBUG));
$httpClient = HttpClient::create();
if ($httpClient instanceof LoggerAwareInterface) {
    $httpClient->setLogger($logger);
}
$openAiClient = new OpenAIClient($httpClient, getenv('OPENAI_API_KEY'));
$clock = new Clock(new SymfonyClock());
$chatModel = new ChatModel($openAiClient, temperature: 0.5);
$registry = new Registry(new ToolAnalyzer(new ParameterAnalyzer()), $logger, [$clock]);
$chain = new ToolChain($chatModel, $registry);
$response = $chain->call(Message::ofUser('What date and time is it?'), new MessageBag());

var_dump($response);
