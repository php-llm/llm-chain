<?php

use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\OpenAI\Model\Gpt;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;
use PhpLlm\LlmChain\OpenAI\Runtime\OpenAI;
use PhpLlm\LlmChain\ToolBox\ParameterAnalyzer;
use PhpLlm\LlmChain\ToolBox\Registry;
use PhpLlm\LlmChain\ToolBox\Tool\YouTubeTranscriber;
use PhpLlm\LlmChain\ToolBox\ToolAnalyzer;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';

$httpClient = HttpClient::create();
$runtime = new OpenAI($httpClient, getenv('OPENAI_API_KEY'));
$llm = new Gpt($runtime, Version::GPT_4o_MINI);

$transcriber = new YouTubeTranscriber($httpClient);
$registry = new Registry(new ToolAnalyzer(new ParameterAnalyzer()), [$transcriber]);
$chain = new Chain($llm, $registry);

$messages = new MessageBag(Message::ofUser('Please summarize this video for me: https://www.youtube.com/watch?v=6uXW-ulpj0s'));
$response = $chain->call($messages);

echo $response.PHP_EOL;
