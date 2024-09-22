<?php

use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\OpenAI\Model\Gpt;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;
use PhpLlm\LlmChain\OpenAI\Runtime\OpenAI;
use PhpLlm\LlmChain\ToolBox\ParameterAnalyzer;
use PhpLlm\LlmChain\ToolBox\Tool\Wikipedia;
use PhpLlm\LlmChain\ToolBox\ToolAnalyzer;
use PhpLlm\LlmChain\ToolBox\Toolbox;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';

$httpClient = HttpClient::create();
$runtime = new OpenAI($httpClient, getenv('OPENAI_API_KEY'));
$llm = new Gpt($runtime, Version::GPT_4o_MINI);

$wikipedia = new Wikipedia($httpClient);
$registry = new Toolbox(new ToolAnalyzer(new ParameterAnalyzer()), [$wikipedia]);
$chain = new Chain($llm, $registry);

$messages = new MessageBag(Message::ofUser('Who is the current chancellor of Germany?'));
$response = $chain->call($messages);

echo $response.PHP_EOL;
