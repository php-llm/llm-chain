<?php

use Symfony\Component\HttpClient\HttpClient;
use SymfonyLlm\LlmChain\Message\Message;
use SymfonyLlm\LlmChain\Message\MessageBag;
use SymfonyLlm\LlmChain\OpenAI\ChatModel;
use SymfonyLlm\LlmChain\OpenAI\OpenAIClient;
use SymfonyLlm\LlmChain\ToolBox\ParameterAnalyzer;
use SymfonyLlm\LlmChain\ToolBox\Registry;
use SymfonyLlm\LlmChain\ToolBox\Tool\SerpApi;
use SymfonyLlm\LlmChain\ToolBox\ToolAnalyzer;
use SymfonyLlm\LlmChain\ToolChain;

require_once __DIR__.'/../vendor/autoload.php';

$httpClient = HttpClient::create();
$openAiClient = new OpenAIClient($httpClient, getenv('OPENAI_API_KEY'));
$serpApi = new SerpApi($httpClient, getenv('SERP_API_KEY'));
$chatModel = new ChatModel($openAiClient, temperature: 0.5);
$registry = new Registry(new ToolAnalyzer(new ParameterAnalyzer()), [$serpApi]);
$chain = new ToolChain($chatModel, $registry);
$response = $chain->call(Message::ofUser('Who is the current chancellor of Germany?'), new MessageBag());

var_dump($response);
