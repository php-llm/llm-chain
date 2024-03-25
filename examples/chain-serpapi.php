<?php

use Symfony\Component\HttpClient\HttpClient;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\OpenAI\ChatModel;
use PhpLlm\LlmChain\OpenAI\OpenAIClient;
use PhpLlm\LlmChain\ToolBox\ParameterAnalyzer;
use PhpLlm\LlmChain\ToolBox\Registry;
use PhpLlm\LlmChain\ToolBox\Tool\SerpApi;
use PhpLlm\LlmChain\ToolBox\ToolAnalyzer;
use PhpLlm\LlmChain\ToolChain;

require_once dirname(__DIR__).'/vendor/autoload.php';

$httpClient = HttpClient::create();
$openAiClient = new OpenAIClient($httpClient, getenv('OPENAI_API_KEY'));
$serpApi = new SerpApi($httpClient, getenv('SERP_API_KEY'));
$chatModel = new ChatModel($openAiClient, temperature: 0.5);
$registry = new Registry(new ToolAnalyzer(new ParameterAnalyzer()), [$serpApi]);
$chain = new ToolChain($chatModel, $registry);
$response = $chain->call(Message::ofUser('Who is the current chancellor of Germany?'), new MessageBag());

var_dump($response);
