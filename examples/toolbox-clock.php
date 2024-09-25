<?php

use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\OpenAI\Model\Gpt;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;
use PhpLlm\LlmChain\OpenAI\Platform\OpenAI;
use PhpLlm\LlmChain\ToolBox\Tool\Clock;
use PhpLlm\LlmChain\ToolBox\ToolAnalyzer;
use PhpLlm\LlmChain\ToolBox\ToolBox;
use Symfony\Component\Clock\Clock as SymfonyClock;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';

$platform = new OpenAI(HttpClient::create(), getenv('OPENAI_API_KEY'));
$llm = new Gpt($platform, Version::gpt4oMini());

$clock = new Clock(new SymfonyClock());
$toolBox = new ToolBox(new ToolAnalyzer(), [$clock]);
$chain = new Chain($llm, $toolBox);

$messages = new MessageBag(Message::ofUser('What date and time is it?'));
$response = $chain->call($messages);

echo $response.PHP_EOL;
