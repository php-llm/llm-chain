<?php

use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Message\UserMessage;
use PhpLlm\LlmChain\OpenAI\Model\Gpt;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;
use PhpLlm\LlmChain\OpenAI\Platform\OpenAI;
use PhpLlm\LlmChain\ToolBox\ChainProcessor;
use PhpLlm\LlmChain\ToolBox\Tool\Clock;
use PhpLlm\LlmChain\ToolBox\ToolAnalyzer;
use PhpLlm\LlmChain\ToolBox\ToolBox;
use Symfony\Component\Clock\Clock as SymfonyClock;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['OPENAI_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = new OpenAI(HttpClient::create(), $_ENV['OPENAI_API_KEY']);
$llm = new Gpt($platform, Version::gpt4oMini());

$clock = new Clock(new SymfonyClock());
$toolBox = new ToolBox(new ToolAnalyzer(), [$clock]);
$processor = new ChainProcessor($toolBox);
$chain = new Chain($llm, [$processor], [$processor]);

$messages = new MessageBag(new UserMessage('What date and time is it?'));
$response = $chain->call($messages);

echo $response->getContent().PHP_EOL;
