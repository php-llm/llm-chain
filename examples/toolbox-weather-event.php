<?php

use PhpLlm\LlmChain\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Bridge\OpenAI\PlatformFactory;
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Chain\ToolBox\ChainProcessor;
use PhpLlm\LlmChain\Chain\ToolBox\Event\ToolCallsExecuted;
use PhpLlm\LlmChain\Chain\ToolBox\Tool\OpenMeteo;
use PhpLlm\LlmChain\Chain\ToolBox\ToolAnalyzer;
use PhpLlm\LlmChain\Chain\ToolBox\ToolBox;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use PhpLlm\LlmChain\Model\Response\StructuredResponse;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['OPENAI_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);
$llm = new GPT(GPT::GPT_4O_MINI);

$openMeteo = new OpenMeteo(HttpClient::create());
$toolBox = new ToolBox(new ToolAnalyzer(), [$openMeteo]);
$eventDispatcher = new EventDispatcher();
$processor = new ChainProcessor($toolBox, eventDispatcher: $eventDispatcher);
$chain = new Chain($platform, $llm, [$processor], [$processor]);

// Add tool call result listener to enforce chain exits direct with structured response for weather tools
$eventDispatcher->addListener(ToolCallsExecuted::class, function (ToolCallsExecuted $event): void {
    foreach ($event->toolCallResults as $toolCallResult) {
        if (str_starts_with($toolCallResult->toolCall->name, 'weather_')) {
            $event->response = new StructuredResponse($toolCallResult->result);
        }
    }
});

$messages = new MessageBag(Message::ofUser('How is the weather currently in Berlin?'));
$response = $chain->call($messages);

dump($response->getContent());
