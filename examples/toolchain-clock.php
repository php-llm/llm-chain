<?php

use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\OpenAI\Model\Gpt;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;
use PhpLlm\LlmChain\OpenAI\Runtime\OpenAI;
use PhpLlm\LlmChain\ToolBox\ParameterAnalyzer;
use PhpLlm\LlmChain\ToolBox\Registry;
use PhpLlm\LlmChain\ToolBox\Tool\Clock;
use PhpLlm\LlmChain\ToolBox\ToolAnalyzer;
use PhpLlm\LlmChain\ToolChain;
use Psr\Log\NullLogger;
use Symfony\Component\Clock\Clock as SymfonyClock;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';

$runtime = new OpenAI(HttpClient::create(), getenv('OPENAI_API_KEY'));
$llm = new Gpt($runtime, Version::GPT_4o_MINI);

$clock = new Clock(new SymfonyClock());
$registry = new Registry(new ToolAnalyzer(new ParameterAnalyzer()), new NullLogger(), [$clock]);
$chain = new ToolChain($llm, $registry);

$messages = new MessageBag(Message::ofUser('What date and time is it?'));
$response = $chain->call($messages);

echo $response.PHP_EOL;
